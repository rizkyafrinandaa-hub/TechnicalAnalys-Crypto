// trading-app.js - Cross-X Pro Trading Application Logic

class TradingPlatform {
    constructor() {
        this.symbol = 'BTCUSDT';
        this.interval = '4h';
        this.sensitivity = 25;
        this.candleData = [];
        this.indicators = {
            sma20: [],
            sma50: [],
            ema9: [],
            ema21: [],
            macd: [],
            rsi: [],
            atr: [],
            psar: [],
            stochastic: [],
            volume: []
        };
        this.signals = [];
        this.divergences = [];
        this.swingPoints = [];
        this.ws = null;
        
        this.init();
    }
    
    init() {
        this.setupChart();
        this.setupEventListeners();
        this.loadHistoricalData();
    }
    
    setupChart() {
        const chartContainer = document.getElementById('tradingChart');
        this.chart = LightweightCharts.createChart(chartContainer, {
            width: chartContainer.clientWidth,
            height: chartContainer.clientHeight - 20,
            layout: {
                background: { color: '#131722' },
                textColor: '#d1d4dc',
            },
            grid: {
                vertLines: { color: '#1e222d' },
                horzLines: { color: '#1e222d' },
            },
            crosshair: {
                mode: LightweightCharts.CrosshairMode.Normal,
            },
            rightPriceScale: {
                borderColor: '#2a2e39',
            },
            timeScale: {
                borderColor: '#2a2e39',
                timeVisible: true,
                secondsVisible: false,
            },
        });
        
        // Candlestick series
        this.candleSeries = this.chart.addCandlestickSeries({
            upColor: '#26a69a',
            downColor: '#ef5350',
            borderVisible: false,
            wickUpColor: '#26a69a',
            wickDownColor: '#ef5350',
        });
        
        // EMA 9 (short-term)
        this.ema9Series = this.chart.addLineSeries({
            color: '#2962ff',
            lineWidth: 2,
            title: 'EMA 9',
        });
        
        // EMA 21 (medium-term)
        this.ema21Series = this.chart.addLineSeries({
            color: '#ff6d00',
            lineWidth: 2,
            title: 'EMA 21',
        });
        
        // SMA 50 (long-term trend)
        this.sma50Series = this.chart.addLineSeries({
            color: '#ab47bc',
            lineWidth: 2,
            title: 'SMA 50',
        });
        
        // Volume series
        this.volumeSeries = this.chart.addHistogramSeries({
            color: '#26a69a',
            priceFormat: {
                type: 'volume',
            },
            priceScaleId: 'volume',
        });
        
        this.chart.priceScale('volume').applyOptions({
            scaleMargins: {
                top: 0.8,
                bottom: 0,
            },
        });
        
        // Responsive
        window.addEventListener('resize', () => {
            this.chart.applyOptions({
                width: chartContainer.clientWidth,
                height: chartContainer.clientHeight - 20,
            });
        });
    }
    
    setupEventListeners() {
        document.getElementById('symbolSelect').addEventListener('change', (e) => {
            this.symbol = e.target.value;
            this.restart();
        });
        
        document.getElementById('intervalSelect').addEventListener('change', (e) => {
            this.interval = e.target.value;
            this.restart();
        });
        
        document.getElementById('sensitivity').addEventListener('change', (e) => {
            this.sensitivity = parseInt(e.target.value);
            this.recalculateSignals();
        });
    }
    
    async loadHistoricalData() {
        document.getElementById('loadingIndicator').style.display = 'block';
        
        try {
            const limit = 500; // Get 500 candles
            const url = `https://api.binance.com/api/v3/klines?symbol=${this.symbol}&interval=${this.interval}&limit=${limit}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            this.candleData = data.map(d => ({
                time: d[0] / 1000,
                open: parseFloat(d[1]),
                high: parseFloat(d[2]),
                low: parseFloat(d[3]),
                close: parseFloat(d[4]),
                volume: parseFloat(d[5])
            }));
            
            this.calculateAllIndicators();
            this.detectSignals();
            this.detectDivergences();
            this.detectSwingPoints();
            this.updateChart();
            this.updateUI();
            this.connectWebSocket();
            
            document.getElementById('loadingIndicator').style.display = 'none';
        } catch (error) {
            console.error('Error loading data:', error);
            alert('Failed to load market data. Please try again.');
        }
    }
    
    calculateAllIndicators() {
        this.calculateSMA(20);
        this.calculateSMA(50);
        this.calculateEMA(9);
        this.calculateEMA(21);
        this.calculateMACD();
        this.calculateRSI(14);
        this.calculateATR(14);
        this.calculatePSAR();
        this.calculateStochastic(14, 3, 3);
    }
    
    calculateSMA(period) {
        const result = [];
        for (let i = 0; i < this.candleData.length; i++) {
            if (i < period - 1) {
                result.push(null);
                continue;
            }
            
            let sum = 0;
            for (let j = 0; j < period; j++) {
                sum += this.candleData[i - j].close;
            }
            result.push(sum / period);
        }
        
        if (period === 20) this.indicators.sma20 = result;
        if (period === 50) this.indicators.sma50 = result;
    }
    
    calculateEMA(period) {
        const result = [];
        const multiplier = 2 / (period + 1);
        let ema = 0;
        
        // Start with SMA for first value
        for (let i = 0; i < period; i++) {
            ema += this.candleData[i].close;
        }
        ema = ema / period;
        result.push(ema);
        
        // Calculate EMA for remaining values
        for (let i = period; i < this.candleData.length; i++) {
            ema = (this.candleData[i].close - ema) * multiplier + ema;
            result.push(ema);
        }
        
        // Pad beginning with nulls
        for (let i = 0; i < period - 1; i++) {
            result.unshift(null);
        }
        
        if (period === 9) this.indicators.ema9 = result;
        if (period === 21) this.indicators.ema21 = result;
    }
    
    calculateMACD() {
        const ema12 = this.calculateEMAArray(this.candleData.map(d => d.close), 12);
        const ema26 = this.calculateEMAArray(this.candleData.map(d => d.close), 26);
        const macdLine = ema12.map((val, idx) => val !== null && ema26[idx] !== null ? val - ema26[idx] : null);
        const signalLine = this.calculateEMAArray(macdLine.filter(v => v !== null), 9);
        
        // Pad signal line
        const paddingLength = macdLine.length - signalLine.length;
        for (let i = 0; i < paddingLength; i++) {
            signalLine.unshift(null);
        }
        
        const histogram = macdLine.map((val, idx) => 
            val !== null && signalLine[idx] !== null ? val - signalLine[idx] : null
        );
        
        this.indicators.macd = macdLine.map((val, idx) => ({
            macd: val,
            signal: signalLine[idx],
            histogram: histogram[idx]
        }));
    }
    
    calculateEMAArray(data, period) {
        const result = [];
        const multiplier = 2 / (period + 1);
        let ema = 0;
        
        for (let i = 0; i < period && i < data.length; i++) {
            if (data[i] !== null) ema += data[i];
        }
        ema = ema / period;
        result.push(ema);
        
        for (let i = period; i < data.length; i++) {
            if (data[i] !== null) {
                ema = (data[i] - ema) * multiplier + ema;
                result.push(ema);
            }
        }
        
        return result;
    }
    
    calculateRSI(period) {
        const result = [];
        let gains = 0, losses = 0;
        
        // First RSI value
        for (let i = 1; i <= period; i++) {
            const change = this.candleData[i].close - this.candleData[i - 1].close;
            if (change > 0) gains += change;
            else losses += Math.abs(change);
        }
        
        let avgGain = gains / period;
        let avgLoss = losses / period;
        let rs = avgGain / avgLoss;
        result.push(100 - (100 / (1 + rs)));
        
        // Subsequent RSI values
        for (let i = period + 1; i < this.candleData.length; i++) {
            const change = this.candleData[i].close - this.candleData[i - 1].close;
            const gain = change > 0 ? change : 0;
            const loss = change < 0 ? Math.abs(change) : 0;
            
            avgGain = (avgGain * (period - 1) + gain) / period;
            avgLoss = (avgLoss * (period - 1) + loss) / period;
            rs = avgGain / avgLoss;
            result.push(100 - (100 / (1 + rs)));
        }
        
        // Pad beginning
        for (let i = 0; i <= period; i++) {
            result.unshift(null);
        }
        
        this.indicators.rsi = result;
    }
    
    calculateATR(period) {
        const result = [];
        const trueRanges = [];
        
        // Calculate True Range
        for (let i = 1; i < this.candleData.length; i++) {
            const high = this.candleData[i].high;
            const low = this.candleData[i].low;
            const prevClose = this.candleData[i - 1].close;
            
            const tr = Math.max(
                high - low,
                Math.abs(high - prevClose),
                Math.abs(low - prevClose)
            );
            trueRanges.push(tr);
        }
        
        // Calculate ATR
        let atr = trueRanges.slice(0, period).reduce((a, b) => a + b) / period;
        result.push(atr);
        
        for (let i = period; i < trueRanges.length; i++) {
            atr = (atr * (period - 1) + trueRanges[i]) / period;
            result.push(atr);
        }
        
        // Pad beginning
        for (let i = 0; i <= period; i++) {
            result.unshift(null);
        }
        
        this.indicators.atr = result;
    }
    
    calculatePSAR() {
        const result = [];
        let af = 0.02;
        const maxAF = 0.2;
        let trend = 1; // 1 for uptrend, -1 for downtrend
        let sar = this.candleData[0].low;
        let ep = this.candleData[0].high;
        
        result.push(sar);
        
        for (let i = 1; i < this.candleData.length; i++) {
            const candle = this.candleData[i];
            
            // Calculate new SAR
            sar = sar + af * (ep - sar);
            
            // Check for reversal
            if (trend === 1) {
                if (candle.low < sar) {
                    trend = -1;
                    sar = ep;
                    ep = candle.low;
                    af = 0.02;
                } else {
                    if (candle.high > ep) {
                        ep = candle.high;
                        af = Math.min(af + 0.02, maxAF);
                    }
                }
            } else {
                if (candle.high > sar) {
                    trend = 1;
                    sar = ep;
                    ep = candle.high;
                    af = 0.02;
                } else {
                    if (candle.low < ep) {
                        ep = candle.low;
                        af = Math.min(af + 0.02, maxAF);
                    }
                }
            }
            
            result.push({ value: sar, trend: trend });
        }
        
        this.indicators.psar = result;
    }
    
    calculateStochastic(kPeriod, kSmooth, dPeriod) {
        const result = [];
        
        for (let i = kPeriod - 1; i < this.candleData.length; i++) {
            const slice = this.candleData.slice(i - kPeriod + 1, i + 1);
            const high = Math.max(...slice.map(d => d.high));
            const low = Math.min(...slice.map(d => d.low));
            const close = this.candleData[i].close;
            
            const k = ((close - low) / (high - low)) * 100;
            result.push(k);
        }
        
        // Smooth %K
        const smoothK = [];
        for (let i = kSmooth - 1; i < result.length; i++) {
            const sum = result.slice(i - kSmooth + 1, i + 1).reduce((a, b) => a + b);
            smoothK.push(sum / kSmooth);
        }
        
        // Calculate %D
        const d = [];
        for (let i = dPeriod - 1; i < smoothK.length; i++) {
            const sum = smoothK.slice(i - dPeriod + 1, i + 1).reduce((a, b) => a + b);
            d.push(sum / dPeriod);
        }
        
        this.indicators.stochastic = smoothK.map((k, idx) => ({
            k: k,
            d: d[idx] || null
        }));
    }
    
    detectSignals() {
        this.signals = [];
        const lookback = 5;
        
        for (let i = lookback; i < this.candleData.length; i++) {
            const signal = this.calculateSignal(i);
            this.signals.push(signal);
        }
    }
    
    calculateSignal(index) {
        let score = 0;
        const sensitivity = this.sensitivity / 10; // Normalize to 0-5
        
        // Price vs EMA (trend following)
        if (this.indicators.ema9[index] && this.indicators.ema21[index]) {
            if (this.candleData[index].close > this.indicators.ema9[index]) score += 1.5;
            if (this.candleData[index].close < this.indicators.ema9[index]) score -= 1.5;
            
            if (this.indicators.ema9[index] > this.indicators.ema21[index]) score += 1;
            if (this.indicators.ema9[index] < this.indicators.ema21[index]) score -= 1;
        }
        
        // MACD
        const macd = this.indicators.macd[index];
        if (macd && macd.histogram !== null) {
            if (macd.histogram > 0) score += 1;
            if (macd.histogram < 0) score -= 1;
            
            // MACD crossover
            const prevMACD = this.indicators.macd[index - 1];
            if (prevMACD && macd.macd > macd.signal && prevMACD.macd <= prevMACD.signal) {
                score += 2; // Bullish crossover
            }
            if (prevMACD && macd.macd < macd.signal && prevMACD.macd >= prevMACD.signal) {
                score -= 2; // Bearish crossover
            }
        }
        
        // RSI
        const rsi = this.indicators.rsi[index];
        if (rsi !== null) {
            if (rsi < 30) score += 1.5; // Oversold
            if (rsi > 70) score -= 1.5; // Overbought
            if (rsi > 50) score += 0.5;
            if (rsi < 50) score -= 0.5;
        }
        
        // PSAR
        const psar = this.indicators.psar[index];
        if (psar) {
            if (psar.trend === 1) score += 1;
            if (psar.trend === -1) score -= 1;
        }
        
        // Volume confirmation
        if (index >= 20) {
            const avgVolume = this.candleData.slice(index - 20, index)
                .reduce((sum, d) => sum + d.volume, 0) / 20;
            const currentVolume = this.candleData[index].volume;
            
            if (currentVolume > avgVolume * 1.5 && score > 0) score += 1;
            if (currentVolume > avgVolume * 1.5 && score < 0) score -= 1;
        }
        
        // Volatility (ATR)
        const atr = this.indicators.atr[index];
        if (atr !== null && index > 0) {
            const prevATR = this.indicators.atr[index - 1];
            if (prevATR && atr > prevATR * 1.2) {
                // High volatility - reduce confidence
                score *= 0.8;
            }
        }
        
        // Apply sensitivity threshold
        const threshold = 3 - (sensitivity * 0.4);
        
        let signalType = 'HOLD';
        if (score >= threshold) signalType = 'BUY';
        if (score <= -threshold) signalType = 'SELL';
        
        return {
            time: this.candleData[index].time,
            type: signalType,
            score: score,
            strength: Math.abs(score)
        };
    }
    
    detectDivergences() {
        this.divergences = [];
        const lookback = 50;
        
        if (this.candleData.length < lookback) return;
        
        // Detect price and RSI divergences
        for (let i = lookback; i < this.candleData.length - 5; i++) {
            // Bullish divergence: price making lower low, RSI making higher low
            if (this.indicators.rsi[i] && this.indicators.rsi[i - lookback]) {
                const priceLow1 = Math.min(...this.candleData.slice(i - lookback, i - lookback + 10).map(d => d.low));
                const priceLow2 = Math.min(...this.candleData.slice(i - 5, i + 5).map(d => d.low));
                
                const rsiLow1 = Math.min(...this.indicators.rsi.slice(i - lookback, i - lookback + 10).filter(v => v !== null));
                const rsiLow2 = Math.min(...this.indicators.rsi.slice(i - 5, i + 5).filter(v => v !== null));
                
                if (priceLow2 < priceLow1 && rsiLow2 > rsiLow1) {
                    this.divergences.push({
                        time: this.candleData[i].time,
                        type: 'Bullish Regular',
                        indicator: 'RSI'
                    });
                }
                
                // Bearish divergence: price making higher high, RSI making lower high
                const priceHigh1 = Math.max(...this.candleData.slice(i - lookback, i - lookback + 10).map(d => d.high));
                const priceHigh2 = Math.max(...this.candleData.slice(i - 5, i + 5).map(d => d.high));
                
                const rsiHigh1 = Math.max(...this.indicators.rsi.slice(i - lookback, i - lookback + 10).filter(v => v !== null));
                const rsiHigh2 = Math.max(...this.indicators.rsi.slice(i - 5, i + 5).filter(v => v !== null));
                
                if (priceHigh2 > priceHigh1 && rsiHigh2 < rsiHigh1) {
                    this.divergences.push({
                        time: this.candleData[i].time,
                        type: 'Bearish Regular',
                        indicator: 'RSI'
                    });
                }
            }
        }
    }
    
    detectSwingPoints() {
        this.swingPoints = [];
        const pivotLength = 5;
        
        for (let i = pivotLength; i < this.candleData.length - pivotLength; i++) {
            const slice = this.candleData.slice(i - pivotLength, i + pivotLength + 1);
            const current = this.candleData[i];
            
            // Swing High
            const isSwingHigh = slice.every(d => d !== current ? d.high <= current.high : true);
            
            // Swing Low
            const isSwingLow = slice.every(d => d !== current ? d.low >= current.low : true);
            
            if (isSwingHigh || isSwingLow) {
                // Determine structure (HH, HL, LH, LL)
                let structure = '';
                if (i >= pivotLength * 2) {
                    const prevSwings = this.swingPoints.slice(-2);
                    if (prevSwings.length >= 1) {
                        const prev = prevSwings[prevSwings.length - 1];
                        if (isSwingHigh) {
                            structure = current.high > prev.price ? 'HH' : 'LH';
                        } else {
                            structure = current.low < prev.price ? 'LL' : 'HL';
                        }
                    }
                }
                
                this.swingPoints.push({
                    time: current.time,
                    price: isSwingHigh ? current.high : current.low,
                    type: isSwingHigh ? 'high' : 'low',
                    structure: structure
                });
            }
        }
    }
    
    updateChart() {
        // Update candlestick data
        this.candleSeries.setData(this.candleData);
        
        // Update EMAs
        const ema9Data = this.candleData.map((d, i) => ({
            time: d.time,
            value: this.indicators.ema9[i]
        })).filter(d => d.value !== null);
        this.ema9Series.setData(ema9Data);
        
        const ema21Data = this.candleData.map((d, i) => ({
            time: d.time,
            value: this.indicators.ema21[i]
        })).filter(d => d.value !== null);
        this.ema21Series.setData(ema21Data);
        
        const sma50Data = this.candleData.map((d, i) => ({
            time: d.time,
            value: this.indicators.sma50[i]
        })).filter(d => d.value !== null);
        this.sma50Series.setData(sma50Data);
        
        // Update volume
        const volumeData = this.candleData.map(d => ({
            time: d.time,
            value: d.volume,
            color: d.close >= d.open ? 'rgba(38, 166, 154, 0.5)' : 'rgba(239, 83, 80, 0.5)'
        }));
        this.volumeSeries.setData(volumeData);
        
        // Add buy/sell markers
        const markers = [];
        this.signals.forEach(signal => {
            if (signal.type === 'BUY') {
                markers.push({
                    time: signal.time,
                    position: 'belowBar',
                    color: '#26a69a',
                    shape: 'arrowUp',
                    text: 'BUY'
                });
            } else if (signal.type === 'SELL') {
                markers.push({
                    time: signal.time,
                    position: 'aboveBar',
                    color: '#ef5350',
                    shape: 'arrowDown',
                    text: 'SELL'
                });
            }
        });
        this.candleSeries.setMarkers(markers);
        
        this.chart.timeScale().fitContent();
    }
    
    updateUI() {
        const latest = this.candleData[this.candleData.length - 1];
        const latestSignal = this.signals[this.signals.length - 1];
        const latestRSI = this.indicators.rsi[this.indicators.rsi.length - 1];
        const latestMACD = this.indicators.macd[this.indicators.macd.length - 1];
        const latestStoch = this.indicators.stochastic[this.indicators.stochastic.length - 1];
        
        // Current Price
        document.getElementById('currentPrice').textContent = `$${latest.close.toFixed(2)}`;
        
        // Price Change
        const first = this.candleData[0];
        const changePercent = ((latest.close - first.close) / first.close * 100).toFixed(2);
        const changeEl = document.getElementById('priceChange');
        changeEl.textContent = `${changePercent}%`;
        changeEl.className = `value ${changePercent >= 0 ? 'bullish' : 'bearish'}`;
        
        // Current Signal
        if (latestSignal) {
            const signalEl = document.getElementById('currentSignal');
            signalEl.textContent = latestSignal.type;
            signalEl.className = `signal-badge signal-${latestSignal.type.toLowerCase()}`;
        }
        
        // Macro Trend
        const ema9 = this.indicators.ema9[this.indicators.ema9.length - 1];
        const ema21 = this.indicators.ema21[this.indicators.ema21.length - 1];
        const sma50 = this.indicators.sma50[this.indicators.sma50.length - 1];
        
        let trendDirection = 'Neutral';
        let trendClass = 'neutral';
        if (ema9 && ema21 && sma50) {
            if (ema9 > ema21 && ema21 > sma50) {
                trendDirection = 'Strong Uptrend';
                trendClass = 'bullish';
            } else if (ema9 < ema21 && ema21 < sma50) {
                trendDirection = 'Strong Downtrend';
                trendClass = 'bearish';
            } else if (ema9 > ema21) {
                trendDirection = 'Weak Uptrend';
                trendClass = 'bullish';
            } else if (ema9 < ema21) {
                trendDirection = 'Weak Downtrend';
                trendClass = 'bearish';
            }
        }
        
        const trendEl = document.getElementById('macroTrend');
        trendEl.textContent = trendDirection;
        trendEl.className = `value ${trendClass}`;
        
        // Trend Strength
        let strength = 'Weak';
        if (latestSignal && latestSignal.strength > 5) strength = 'Strong';
        else if (latestSignal && latestSignal.strength > 3) strength = 'Medium';
        document.getElementById('trendStrength').textContent = strength;
        
        // MACD
        if (latestMACD && latestMACD.histogram !== null) {
            const macdEl = document.getElementById('macdValue');
            const macdText = latestMACD.histogram > 0 ? 'Bullish' : 'Bearish';
            const macdClass = latestMACD.histogram > 0 ? 'bullish' : 'bearish';
            macdEl.textContent = `${macdText} (${latestMACD.histogram.toFixed(4)})`;
            macdEl.className = `value ${macdClass}`;
        }
        
        // RSI
        if (latestRSI !== null) {
            const rsiEl = document.getElementById('rsiValue');
            let rsiClass = 'neutral';
            if (latestRSI > 70) rsiClass = 'bearish';
            else if (latestRSI < 30) rsiClass = 'bullish';
            rsiEl.textContent = latestRSI.toFixed(2);
            rsiEl.className = `value ${rsiClass}`;
        }
        
        // Momentum
        const momentumEl = document.getElementById('momentumStatus');
        let momentum = 'Neutral';
        let momentumClass = 'neutral';
        if (latestMACD && latestRSI !== null) {
            if (latestMACD.histogram > 0 && latestRSI > 50) {
                momentum = 'Bullish';
                momentumClass = 'bullish';
            } else if (latestMACD.histogram < 0 && latestRSI < 50) {
                momentum = 'Bearish';
                momentumClass = 'bearish';
            }
        }
        momentumEl.textContent = momentum;
        momentumEl.className = `value ${momentumClass}`;
        
        // Market Structure
        const recentSwings = this.swingPoints.slice(-5);
        const structureEl = document.getElementById('marketStructure');
        if (recentSwings.length >= 2) {
            const last = recentSwings[recentSwings.length - 1];
            structureEl.textContent = last.structure || 'Building';
        } else {
            structureEl.textContent = 'Insufficient Data';
        }
        
        // Swing Points
        const swingPointsHTML = recentSwings.map(s => 
            `<span class="structure-label ${s.structure.toLowerCase()}">${s.structure}</span>`
        ).join('');
        document.getElementById('swingPoints').innerHTML = swingPointsHTML;
        
        // OB/OS Status
        if (latestRSI !== null) {
            const obosEl = document.getElementById('obosStatus');
            let obos = 'Neutral';
            let obosClass = 'neutral';
            if (latestRSI > 70) {
                obos = 'Overbought';
                obosClass = 'bearish';
            } else if (latestRSI < 30) {
                obos = 'Oversold';
                obosClass = 'bullish';
            }
            obosEl.textContent = obos;
            obosEl.className = `value ${obosClass}`;
        }
        
        // Stochastic
        if (latestStoch && latestStoch.k !== null) {
            document.getElementById('stochValue').textContent = `${latestStoch.k.toFixed(2)}`;
        }
        
        // Volume Order Flow
        const recentVolumes = this.candleData.slice(-20).map(d => d.volume);
        const avgVolume = recentVolumes.reduce((a, b) => a + b) / recentVolumes.length;
        const currentVolume = latest.volume;
        
        const volumeTrendEl = document.getElementById('volumeTrend');
        if (currentVolume > avgVolume * 1.5) {
            volumeTrendEl.textContent = 'High';
            volumeTrendEl.className = 'value bullish';
        } else if (currentVolume < avgVolume * 0.5) {
            volumeTrendEl.textContent = 'Low';
            volumeTrendEl.className = 'value bearish';
        } else {
            volumeTrendEl.textContent = 'Normal';
            volumeTrendEl.className = 'value neutral';
        }
        
        // Buying/Selling Pressure
        const recentCandles = this.candleData.slice(-20);
        const bullishCandles = recentCandles.filter(d => d.close > d.open).length;
        const bearishCandles = recentCandles.filter(d => d.close < d.open).length;
        
        const buyPressure = (bullishCandles / recentCandles.length * 100).toFixed(0);
        const sellPressure = (bearishCandles / recentCandles.length * 100).toFixed(0);
        
        document.getElementById('buyPressure').textContent = `${buyPressure}%`;
        document.getElementById('sellPressure').textContent = `${sellPressure}%`;
        
        // Divergences
        const recentDivergences = this.divergences.slice(-5);
        if (recentDivergences.length > 0) {
            const divHTML = recentDivergences.map(div => {
                const divClass = div.type.includes('Bullish') ? 'div-bullish' : 'div-bearish';
                return `
                    <div class="divergence-item ${divClass}">
                        <strong>${div.type}</strong><br>
                        <small>${div.indicator} - ${new Date(div.time * 1000).toLocaleDateString()}</small>
                    </div>
                `;
            }).join('');
            document.getElementById('divergenceList').innerHTML = divHTML;
        } else {
            document.getElementById('divergenceList').innerHTML = 
                '<div style="font-size: 12px; color: #787b86;">No divergences detected</div>';
        }
    }
    
    connectWebSocket() {
        if (this.ws) {
            this.ws.close();
        }
        
        const streamName = `${this.symbol.toLowerCase()}@kline_${this.interval}`;
        this.ws = new WebSocket(`wss://stream.binance.com:9443/ws/${streamName}`);
        
        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            const kline = data.k;
            
            const candle = {
                time: kline.t / 1000,
                open: parseFloat(kline.o),
                high: parseFloat(kline.h),
                low: parseFloat(kline.l),
                close: parseFloat(kline.c),
                volume: parseFloat(kline.v)
            };
            
            // Update last candle if same time, otherwise add new
            if (this.candleData.length > 0 && 
                this.candleData[this.candleData.length - 1].time === candle.time) {
                this.candleData[this.candleData.length - 1] = candle;
            } else {
                this.candleData.push(candle);
                if (this.candleData.length > 500) {
                    this.candleData.shift(); // Keep only last 500 candles
                }
            }
            
            // Update chart
            this.candleSeries.update(candle);
            
            // Recalculate indicators on candle close
            if (kline.x) {
                this.calculateAllIndicators();
                this.detectSignals();
                this.updateChart();
                this.updateUI();
            } else {
                // Just update current price
                document.getElementById('currentPrice').textContent = `$${candle.close.toFixed(2)}`;
            }
        };
        
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
        
        this.ws.onclose = () => {
            console.log('WebSocket closed, reconnecting...');
            setTimeout(() => this.connectWebSocket(), 3000);
        };
    }
    
    recalculateSignals() {
        this.detectSignals();
        this.updateChart();
        this.updateUI();
    }
    
    restart() {
        if (this.ws) {
            this.ws.close();
        }
        this.candleData = [];
        this.signals = [];
        this.divergences = [];
        this.swingPoints = [];
        this.loadHistoricalData();
    }
}

// Initialize the trading platform
document.addEventListener('DOMContentLoaded', () => {
    new TradingPlatform();
});
