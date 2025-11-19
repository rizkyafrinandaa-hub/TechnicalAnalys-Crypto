<?php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Trading Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0e27;
            color: #e0e3eb;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 100%;
            padding: 10px;
        }
        
        .header {
            background: #131722;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .header h1 {
            color: #2962ff;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .control-group {
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        
        .control-group label {
            font-size: 13px;
            color: #b2b5be;
            font-weight: 500;
        }
        
        /* Autocomplete Search Styles */
        .search-container {
            position: relative;
            width: 200px;
        }
        
        #symbolSearch {
            width: 100%;
            background: #1e222d;
            border: 1px solid #2a2e39;
            color: #e0e3eb;
            padding: 8px 35px 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        #symbolSearch:focus {
            outline: none;
            border-color: #2962ff;
            box-shadow: 0 0 0 2px rgba(41, 98, 255, 0.1);
        }
        
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #787b86;
            font-size: 14px;
            pointer-events: none;
        }
        
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1e222d;
            border: 1px solid #2a2e39;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .autocomplete-results::-webkit-scrollbar {
            width: 6px;
        }
        
        .autocomplete-results::-webkit-scrollbar-track {
            background: #131722;
        }
        
        .autocomplete-results::-webkit-scrollbar-thumb {
            background: #2962ff;
            border-radius: 3px;
        }
        
        .autocomplete-item {
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .autocomplete-item:hover,
        .autocomplete-item.active {
            background: #2a2e39;
        }
        
        .autocomplete-item .symbol-name {
            font-weight: 600;
            color: #e0e3eb;
        }
        
        .autocomplete-item .symbol-quote {
            font-size: 11px;
            color: #787b86;
            background: #131722;
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        .loading-symbols {
            padding: 10px;
            text-align: center;
            color: #787b86;
            font-size: 12px;
        }
        
        .no-results {
            padding: 10px;
            text-align: center;
            color: #787b86;
            font-size: 12px;
        }
        
        select, input[type="number"] {
            background: #1e222d;
            border: 1px solid #2a2e39;
            color: #e0e3eb;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        select:hover, input:hover {
            border-color: #2962ff;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #2962ff;
            box-shadow: 0 0 0 2px rgba(41, 98, 255, 0.1);
        }
        
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 10px;
            height: calc(100vh - 130px);
        }
        
        .chart-container {
            background: #131722;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        #tradingChart {
            width: 100%;
            height: 100%;
        }
        
        .sidebar {
            background: #131722;
            border-radius: 8px;
            padding: 15px;
            overflow-y: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #1e222d;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #2962ff;
            border-radius: 3px;
        }
        
        .info-panel {
            background: #1e222d;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 12px;
            border: 1px solid #2a2e39;
        }
        
        .info-panel h3 {
            color: #2962ff;
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
            align-items: center;
        }
        
        .info-row .label {
            color: #787b86;
            font-weight: 500;
        }
        
        .info-row .value {
            font-weight: 600;
        }
        
        .bullish { color: #26a69a !important; }
        .bearish { color: #ef5350 !important; }
        .neutral { color: #b2b5be !important; }
        
        .signal-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .signal-buy {
            background: rgba(38, 166, 154, 0.2);
            color: #26a69a;
            border: 1px solid #26a69a;
        }
        
        .signal-sell {
            background: rgba(239, 83, 80, 0.2);
            color: #ef5350;
            border: 1px solid #ef5350;
        }
        
        .signal-hold {
            background: rgba(178, 181, 190, 0.2);
            color: #b2b5be;
            border: 1px solid #b2b5be;
        }
        
        .divergence-item {
            background: #131722;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 8px;
            border-left: 3px solid;
            font-size: 12px;
        }
        
        .div-bullish { border-left-color: #26a69a; }
        .div-bearish { border-left-color: #ef5350; }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 100;
        }
        
        .spinner {
            border: 3px solid #1e222d;
            border-top: 3px solid #2962ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: #2962ff;
            font-size: 14px;
            font-weight: 600;
        }
        
        .structure-label {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 700;
            display: inline-block;
            margin: 2px;
        }
        
        .hh { background: #26a69a; color: #000; }
        .hl { background: #4caf50; color: #000; }
        .lh { background: #ff9800; color: #000; }
        .ll { background: #ef5350; color: #000; }
        
        .error-message {
            background: #ef5350;
            color: #fff;
            padding: 15px;
            border-radius: 6px;
            margin: 20px;
            display: none;
        }
        
        .popular-pairs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        
        .pair-tag {
            background: #1e222d;
            border: 1px solid #2a2e39;
            color: #b2b5be;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pair-tag:hover {
            background: #2962ff;
            color: #fff;
            border-color: #2962ff;
        }
        
        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .chart-container {
                height: 500px;
            }
            
            .search-container {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <span class="status-indicator"></span>
                📈 Pro Trading
            </h1>
            <div class="controls">
                <div class="control-group">
                    <label>Cari Symbol:</label>
                    <div class="search-container">
                        <input 
                            type="text" 
                            id="symbolSearch" 
                            placeholder="Ketik symbol (BTC, ETH...)" 
                            autocomplete="off"
                        >
                        <span class="search-icon">🔍</span>
                        <div class="autocomplete-results" id="autocompleteResults"></div>
                    </div>
                </div>
                <div class="control-group">
                    <label>Timeframe:</label>
                    <select id="intervalSelect">
                        <option value="15m">15m</option>
                        <option value="1h">1h</option>
                        <option value="4h" selected>4h</option>
                        <option value="12h">12h</option>
                        <option value="1d">1D</option>
                        <option value="3d">3D</option>
                        <option value="1w">1W</option>
                    </select>
                </div>
                <div class="control-group">
                    <label>Sensitivity:</label>
                    <input type="number" id="sensitivity" min="1" max="50" value="25" style="width: 70px;">
                </div>
            </div>
        </div>
        
        <!-- Popular Pairs -->
        <div style="background: #131722; padding: 10px 20px; margin-bottom: 10px; border-radius: 8px;">
            <div style="font-size: 12px; color: #787b86; margin-bottom: 8px;">Popular Pairs:</div>
            <div class="popular-pairs">
                <span class="pair-tag" data-symbol="BTCUSDT">BTC/USDT</span>
                <span class="pair-tag" data-symbol="ETHUSDT">ETH/USDT</span>
                <span class="pair-tag" data-symbol="BNBUSDT">BNB/USDT</span>
                <span class="pair-tag" data-symbol="SOLUSDT">SOL/USDT</span>
                <span class="pair-tag" data-symbol="XRPUSDT">XRP/USDT</span>
                <span class="pair-tag" data-symbol="ADAUSDT">ADA/USDT</span>
                <span class="pair-tag" data-symbol="DOGEUSDT">DOGE/USDT</span>
                <span class="pair-tag" data-symbol="MATICUSDT">MATIC/USDT</span>
            </div>
        </div>
        
        <div class="error-message" id="errorMessage"></div>
        
        <div class="main-layout">
            <div class="chart-container">
                <div id="loadingIndicator" class="loading">
                    <div class="spinner"></div>
                    <div class="loading-text">Loading market data...</div>
                </div>
                <div id="tradingChart"></div>
            </div>
            
            <div class="sidebar">
                <!-- Summary Panel -->
                <div class="info-panel">
                    <h3>📊 Summary</h3>
                    <div class="info-row">
                        <span class="label">Signal:</span>
                        <span class="value"><span id="currentSignal" class="signal-badge signal-hold">HOLD</span></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Price:</span>
                        <span class="value" id="currentPrice">Loading...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Change:</span>
                        <span class="value neutral" id="priceChange">-</span>
                    </div>
                </div>
                
                <!-- Macro Trend -->
                <div class="info-panel">
                    <h3>🌍 Macro Trend</h3>
                    <div class="info-row">
                        <span class="label">Direction:</span>
                        <span class="value neutral" id="macroTrend">Analyzing...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Strength:</span>
                        <span class="value neutral" id="trendStrength">-</span>
                    </div>
                </div>
                
                <!-- Momentum -->
                <div class="info-panel">
                    <h3>⚡ Momentum</h3>
                    <div class="info-row">
                        <span class="label">MACD:</span>
                        <span class="value neutral" id="macdValue">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">RSI:</span>
                        <span class="value neutral" id="rsiValue">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status:</span>
                        <span class="value neutral" id="momentumStatus">-</span>
                    </div>
                </div>
                
                <!-- Market Structure -->
                <div class="info-panel">
                    <h3>🏗️ Market Structure</h3>
                    <div class="info-row">
                        <span class="label">Pattern:</span>
                        <span class="value neutral" id="marketStructure">-</span>
                    </div>
                    <div id="swingPoints" style="margin-top: 8px; min-height: 30px;"></div>
                </div>
                
                <!-- Overbought/Oversold -->
                <div class="info-panel">
                    <h3>📉 OB/OS Status</h3>
                    <div class="info-row">
                        <span class="label">Condition:</span>
                        <span class="value neutral" id="obosStatus">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Stochastic:</span>
                        <span class="value neutral" id="stochValue">-</span>
                    </div>
                </div>
                
                <!-- Volume & Order Flow -->
                <div class="info-panel">
                    <h3>📊 Volume Flow</h3>
                    <div class="info-row">
                        <span class="label">Trend:</span>
                        <span class="value neutral" id="volumeTrend">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Buy:</span>
                        <span class="value bullish" id="buyPressure">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Sell:</span>
                        <span class="value bearish" id="sellPressure">-</span>
                    </div>
                </div>
                
                <!-- Divergences -->
                <div class="info-panel">
                    <h3>🔄 Divergences</h3>
                    <div id="divergenceList">
                        <div style="font-size: 12px; color: #787b86;">Scanning...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/lightweight-charts@4.1.3/dist/lightweight-charts.standalone.production.js"></script>
    
    <script>
// trading-app.js - WITH AUTOCOMPLETE SEARCH
console.log('Script loaded successfully');

class TradingPlatform {
    constructor() {
        this.symbol = 'BTCUSDT';
        this.interval = '4h';
        this.sensitivity = 25;
        this.candleData = [];
        this.indicators = {};
        this.signals = [];
        this.divergences = [];
        this.swingPoints = [];
        this.ws = null;
        this.chart = null;
        this.allSymbols = [];
        this.filteredSymbols = [];
        this.activeIndex = -1;
        
        console.log('TradingPlatform initialized');
        this.init();
    }
    
    async init() {
        try {
            console.log('Initializing...');
            await this.loadAllSymbols();
            this.setupChart();
            this.setupEventListeners();
            this.loadHistoricalData();
        } catch (error) {
            console.error('Initialization error:', error);
            this.showError('Failed to initialize: ' + error.message);
        }
    }
    
    async loadAllSymbols() {
        try {
            console.log('Loading all trading symbols...');
            const response = await fetch('https://api.binance.com/api/v3/exchangeInfo');
            const data = await response.json();
            
            // Filter hanya USDT pairs yang TRADING
            this.allSymbols = data.symbols
                .filter(s => s.quoteAsset === 'USDT' && s.status === 'TRADING')
                .map(s => ({
                    symbol: s.symbol,
                    baseAsset: s.baseAsset,
                    quoteAsset: s.quoteAsset
                }))
                .sort((a, b) => a.baseAsset.localeCompare(b.baseAsset));
            
            console.log(`Loaded ${this.allSymbols.length} USDT trading pairs`);
        } catch (error) {
            console.error('Error loading symbols:', error);
            this.showError('Failed to load symbols');
        }
    }
    
    setupEventListeners() {
        const searchInput = document.getElementById('symbolSearch');
        const resultsDiv = document.getElementById('autocompleteResults');
        
        // Search input
        searchInput.addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });
        
        // Keyboard navigation
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.navigateResults(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.navigateResults(-1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.selectActiveResult();
            } else if (e.key === 'Escape') {
                this.hideResults();
            }
        });
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                this.hideResults();
            }
        });
        
        // Popular pairs
        document.querySelectorAll('.pair-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                const symbol = tag.getAttribute('data-symbol');
                this.selectSymbol(symbol);
            });
        });
        
        // Interval
        document.getElementById('intervalSelect').addEventListener('change', (e) => {
            this.interval = e.target.value;
            this.restart();
        });
        
        // Sensitivity
        document.getElementById('sensitivity').addEventListener('change', (e) => {
            this.sensitivity = parseInt(e.target.value);
            if (this.candleData.length > 0) {
                this.recalculateSignals();
            }
        });
    }
    
    handleSearchInput(query) {
        const resultsDiv = document.getElementById('autocompleteResults');
        
        if (!query || query.length < 1) {
            this.hideResults();
            return;
        }
        
        // Filter symbols
        query = query.toUpperCase();
        this.filteredSymbols = this.allSymbols.filter(s => 
            s.baseAsset.includes(query) || s.symbol.includes(query)
        ).slice(0, 50); // Max 50 results
        
        if (this.filteredSymbols.length === 0) {
            resultsDiv.innerHTML = '<div class="no-results">Tidak ada hasil ditemukan</div>';
            resultsDiv.style.display = 'block';
            return;
        }
        
        // Display results
        const html = this.filteredSymbols.map((s, idx) => `
            <div class="autocomplete-item" data-index="${idx}" data-symbol="${s.symbol}">
                <span class="symbol-name">${s.baseAsset}/USDT</span>
                <span class="symbol-quote">${s.symbol}</span>
            </div>
        `).join('');
        
        resultsDiv.innerHTML = html;
        resultsDiv.style.display = 'block';
        this.activeIndex = -1;
        
        // Add click listeners
        resultsDiv.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', () => {
                const symbol = item.getAttribute('data-symbol');
                this.selectSymbol(symbol);
            });
            
            item.addEventListener('mouseenter', () => {
                this.setActiveIndex(parseInt(item.getAttribute('data-index')));
            });
        });
    }
    
    navigateResults(direction) {
        const resultsDiv = document.getElementById('autocompleteResults');
        const items = resultsDiv.querySelectorAll('.autocomplete-item');
        
        if (items.length === 0) return;
        
        this.activeIndex += direction;
        
        if (this.activeIndex < 0) this.activeIndex = items.length - 1;
        if (this.activeIndex >= items.length) this.activeIndex = 0;
        
        this.setActiveIndex(this.activeIndex);
        
        // Scroll into view
        items[this.activeIndex].scrollIntoView({ block: 'nearest' });
    }
    
    setActiveIndex(index) {
        const items = document.querySelectorAll('.autocomplete-item');
        items.forEach((item, idx) => {
            item.classList.toggle('active', idx === index);
        });
        this.activeIndex = index;
    }
    
    selectActiveResult() {
        if (this.activeIndex >= 0 && this.activeIndex < this.filteredSymbols.length) {
            const symbol = this.filteredSymbols[this.activeIndex].symbol;
            this.selectSymbol(symbol);
        }
    }
    
    selectSymbol(symbol) {
        this.symbol = symbol;
        const searchInput = document.getElementById('symbolSearch');
        const symbolData = this.allSymbols.find(s => s.symbol === symbol);
        
        if (symbolData) {
            searchInput.value = `${symbolData.baseAsset}/USDT`;
        }
        
        this.hideResults();
        this.restart();
    }
    
    hideResults() {
        document.getElementById('autocompleteResults').style.display = 'none';
        this.activeIndex = -1;
    }
    
    setupChart() {
        const chartContainer = document.getElementById('tradingChart');
        
        if (!chartContainer) {
            throw new Error('Chart container not found');
        }
        
        if (typeof LightweightCharts === 'undefined') {
            throw new Error('LightweightCharts library not loaded');
        }
        
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
        
        this.candleSeries = this.chart.addCandlestickSeries({
            upColor: '#26a69a',
            downColor: '#ef5350',
            borderVisible: false,
            wickUpColor: '#26a69a',
            wickDownColor: '#ef5350',
        });
        
        this.ema9Series = this.chart.addLineSeries({
            color: '#2962ff',
            lineWidth: 2,
            title: 'EMA 9',
        });
        
        this.ema21Series = this.chart.addLineSeries({
            color: '#ff6d00',
            lineWidth: 2,
            title: 'EMA 21',
        });
        
        this.sma50Series = this.chart.addLineSeries({
            color: '#ab47bc',
            lineWidth: 2,
            title: 'SMA 50',
        });
        
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
        
        window.addEventListener('resize', () => {
            if (this.chart) {
                this.chart.applyOptions({
                    width: chartContainer.clientWidth,
                    height: chartContainer.clientHeight - 20,
                });
            }
        });
        
        console.log('Chart setup completed');
    }
    
    async loadHistoricalData() {
        const loading = document.getElementById('loadingIndicator');
        loading.style.display = 'block';
        
        try {
            console.log(`Loading data for ${this.symbol} ${this.interval}...`);
            
            const limit = 500;
            const url = `https://api.binance.com/api/v3/klines?symbol=${this.symbol}&interval=${this.interval}&limit=${limit}`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!Array.isArray(data) || data.length === 0) {
                throw new Error('No data received');
            }
            
            this.candleData = data.map(d => ({
                time: Math.floor(d[0] / 1000),
                open: parseFloat(d[1]),
                high: parseFloat(d[2]),
                low: parseFloat(d[3]),
                close: parseFloat(d[4]),
                volume: parseFloat(d[5])
            }));
            
            console.log(`Loaded ${this.candleData.length} candles`);
            
            this.calculateAllIndicators();
            this.detectSignals();
            this.detectDivergences();
            this.detectSwingPoints();
            this.updateChart();
            this.updateUI();
            this.connectWebSocket();
            
            loading.style.display = 'none';
            
        } catch (error) {
            console.error('Error loading data:', error);
            loading.style.display = 'none';
            this.showError('Failed to load data: ' + error.message);
        }
    }
    
    calculateAllIndicators() {
        try {
            this.indicators = {
                sma20: this.calculateSMA(20),
                sma50: this.calculateSMA(50),
                ema9: this.calculateEMA(9),
                ema21: this.calculateEMA(21),
                macd: this.calculateMACD(),
                rsi: this.calculateRSI(14),
                atr: this.calculateATR(14),
                psar: this.calculatePSAR(),
                stochastic: this.calculateStochastic(14, 3, 3)
            };
        } catch (error) {
            console.error('Error calculating indicators:', error);
        }
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
        return result;
    }
    
    calculateEMA(period) {
        const result = [];
        const k = 2 / (period + 1);
        let sum = 0;
        for (let i = 0; i < period; i++) {
            sum += this.candleData[i].close;
        }
        let ema = sum / period;
        for (let i = 0; i < period - 1; i++) {
            result.push(null);
        }
        result.push(ema);
        for (let i = period; i < this.candleData.length; i++) {
            ema = this.candleData[i].close * k + ema * (1 - k);
            result.push(ema);
        }
        return result;
    }
    
    calculateMACD() {
        const ema12 = this.calculateEMA(12);
        const ema26 = this.calculateEMA(26);
        const macdLine = ema12.map((val, idx) => 
            val !== null && ema26[idx] !== null ? val - ema26[idx] : null
        );
        const signalLine = [];
        const validMACD = macdLine.filter(v => v !== null);
        const signalPeriod = 9;
        if (validMACD.length >= signalPeriod) {
            let sum = 0;
            for (let i = 0; i < signalPeriod; i++) {
                sum += validMACD[i];
            }
            let ema = sum / signalPeriod;
            signalLine.push(ema);
            const k = 2 / (signalPeriod + 1);
            for (let i = signalPeriod; i < validMACD.length; i++) {
                ema = validMACD[i] * k + ema * (1 - k);
                signalLine.push(ema);
            }
        }
        const padding = macdLine.length - signalLine.length;
        for (let i = 0; i < padding; i++) {
            signalLine.unshift(null);
        }
        const histogram = macdLine.map((val, idx) => 
            val !== null && signalLine[idx] !== null ? val - signalLine[idx] : null
        );
        return macdLine.map((val, idx) => ({
            macd: val,
            signal: signalLine[idx],
            histogram: histogram[idx]
        }));
    }
    
    calculateRSI(period) {
        const result = [];
        let gains = 0, losses = 0;
        for (let i = 1; i <= period; i++) {
            const change = this.candleData[i].close - this.candleData[i - 1].close;
            if (change > 0) gains += change;
            else losses += Math.abs(change);
        }
        let avgGain = gains / period;
        let avgLoss = losses / period;
        let rs = avgGain / (avgLoss || 0.0001);
        for (let i = 0; i <= period; i++) {
            result.push(null);
        }
        result.push(100 - (100 / (1 + rs)));
        for (let i = period + 1; i < this.candleData.length; i++) {
            const change = this.candleData[i].close - this.candleData[i - 1].close;
            const gain = change > 0 ? change : 0;
            const loss = change < 0 ? Math.abs(change) : 0;
            avgGain = (avgGain * (period - 1) + gain) / period;
            avgLoss = (avgLoss * (period - 1) + loss) / period;
            rs = avgGain / (avgLoss || 0.0001);
            result.push(100 - (100 / (1 + rs)));
        }
        return result;
    }
    
    calculateATR(period) {
        const result = [];
        const tr = [];
        for (let i = 1; i < this.candleData.length; i++) {
            const high = this.candleData[i].high;
            const low = this.candleData[i].low;
            const prevClose = this.candleData[i - 1].close;
            tr.push(Math.max(high - low, Math.abs(high - prevClose), Math.abs(low - prevClose)));
        }
        let sum = 0;
        for (let i = 0; i < period; i++) {
            sum += tr[i];
        }
        let atr = sum / period;
        for (let i = 0; i <= period; i++) {
            result.push(null);
        }
        result.push(atr);
        for (let i = period; i < tr.length; i++) {
            atr = (atr * (period - 1) + tr[i]) / period;
            result.push(atr);
        }
        return result;
    }
    
    calculatePSAR() {
        const result = [];
        let af = 0.02;
        const maxAF = 0.2;
        let trend = 1;
        let sar = this.candleData[0].low;
        let ep = this.candleData[0].high;
        result.push({ value: sar, trend: trend });
        for (let i = 1; i < this.candleData.length; i++) {
            const candle = this.candleData[i];
            sar = sar + af * (ep - sar);
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
        return result;
    }
    
    calculateStochastic(kPeriod, kSmooth, dPeriod) {
        const result = [];
        const kValues = [];
        for (let i = kPeriod - 1; i < this.candleData.length; i++) {
            const slice = this.candleData.slice(i - kPeriod + 1, i + 1);
            const high = Math.max(...slice.map(d => d.high));
            const low = Math.min(...slice.map(d => d.low));
            const close = this.candleData[i].close;
            const k = low !== high ? ((close - low) / (high - low)) * 100 : 50;
            kValues.push(k);
        }
        const smoothK = [];
        for (let i = kSmooth - 1; i < kValues.length; i++) {
            const sum = kValues.slice(i - kSmooth + 1, i + 1).reduce((a, b) => a + b);
            smoothK.push(sum / kSmooth);
        }
        for (let i = 0; i < smoothK.length; i++) {
            let d = null;
            if (i >= dPeriod - 1) {
                const sum = smoothK.slice(i - dPeriod + 1, i + 1).reduce((a, b) => a + b);
                d = sum / dPeriod;
            }
            result.push({ k: smoothK[i], d: d });
        }
        return result;
    }
    
    detectSignals() {
        this.signals = [];
        const startIdx = 50;
        for (let i = startIdx; i < this.candleData.length; i++) {
            const signal = this.generateSignal(i);
            this.signals.push(signal);
        }
    }
    
    generateSignal(idx) {
        let score = 0;
        const sens = this.sensitivity / 10;
        const ema9 = this.indicators.ema9[idx];
        const ema21 = this.indicators.ema21[idx];
        const price = this.candleData[idx].close;
        if (ema9 && ema21) {
            if (price > ema9) score += 1.5;
            if (price < ema9) score -= 1.5;
            if (ema9 > ema21) score += 1;
            if (ema9 < ema21) score -= 1;
        }
        const macd = this.indicators.macd[idx];
        if (macd && macd.histogram !== null) {
            if (macd.histogram > 0) score += 1;
            if (macd.histogram < 0) score -= 1;
            if (idx > 0) {
                const prevMACD = this.indicators.macd[idx - 1];
                if (prevMACD && macd.macd > macd.signal && prevMACD.macd <= prevMACD.signal) {
                    score += 2;
                }
                if (prevMACD && macd.macd < macd.signal && prevMACD.macd >= prevMACD.signal) {
                    score -= 2;
                }
            }
        }
        const rsi = this.indicators.rsi[idx];
        if (rsi !== null) {
            if (rsi < 30) score += 1.5;
            if (rsi > 70) score -= 1.5;
            if (rsi > 50) score += 0.5;
            if (rsi < 50) score -= 0.5;
        }
        const psar = this.indicators.psar[idx];
        if (psar) {
            if (psar.trend === 1) score += 1;
            if (psar.trend === -1) score -= 1;
        }
        if (idx >= 20) {
            const avgVol = this.candleData.slice(idx - 20, idx)
                .reduce((sum, d) => sum + d.volume, 0) / 20;
            const currVol = this.candleData[idx].volume;
            if (currVol > avgVol * 1.5 && score > 0) score += 1;
            if (currVol > avgVol * 1.5 && score < 0) score -= 1;
        }
        const threshold = 3 - (sens * 0.4);
        let type = 'HOLD';
        if (score >= threshold) type = 'BUY';
        if (score <= -threshold) type = 'SELL';
        return {
            time: this.candleData[idx].time,
            type: type,
            score: score,
            strength: Math.abs(score)
        };
    }
    
    detectDivergences() {
        this.divergences = [];
        const lookback = 50;
        if (this.candleData.length < lookback + 10) return;
        for (let i = lookback; i < this.candleData.length - 5; i++) {
            if (!this.indicators.rsi[i]) continue;
            const priceLow1 = Math.min(...this.candleData.slice(i - lookback, i - lookback + 10).map(d => d.low));
            const priceLow2 = Math.min(...this.candleData.slice(i - 5, i + 5).map(d => d.low));
            const rsiVals1 = this.indicators.rsi.slice(i - lookback, i - lookback + 10).filter(v => v !== null);
            const rsiVals2 = this.indicators.rsi.slice(i - 5, i + 5).filter(v => v !== null);
            if (rsiVals1.length > 0 && rsiVals2.length > 0) {
                const rsiLow1 = Math.min(...rsiVals1);
                const rsiLow2 = Math.min(...rsiVals2);
                if (priceLow2 < priceLow1 && rsiLow2 > rsiLow1) {
                    this.divergences.push({
                        time: this.candleData[i].time,
                        type: 'Bullish Regular',
                        indicator: 'RSI'
                    });
                }
            }
            const priceHigh1 = Math.max(...this.candleData.slice(i - lookback, i - lookback + 10).map(d => d.high));
            const priceHigh2 = Math.max(...this.candleData.slice(i - 5, i + 5).map(d => d.high));
            if (rsiVals1.length > 0 && rsiVals2.length > 0) {
                const rsiHigh1 = Math.max(...rsiVals1);
                const rsiHigh2 = Math.max(...rsiVals2);
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
        const pivotLen = 5;
        for (let i = pivotLen; i < this.candleData.length - pivotLen; i++) {
            const slice = this.candleData.slice(i - pivotLen, i + pivotLen + 1);
            const curr = this.candleData[i];
            const isHigh = slice.every(d => d === curr || d.high <= curr.high);
            const isLow = slice.every(d => d === curr || d.low >= curr.low);
            if (isHigh || isLow) {
                let struct = '';
                if (this.swingPoints.length >= 1) {
                    const prev = this.swingPoints[this.swingPoints.length - 1];
                    if (isHigh) {
                        struct = curr.high > prev.price ? 'HH' : 'LH';
                    } else {
                        struct = curr.low < prev.price ? 'LL' : 'HL';
                    }
                }
                this.swingPoints.push({
                    time: curr.time,
                    price: isHigh ? curr.high : curr.low,
                    type: isHigh ? 'high' : 'low',
                    structure: struct
                });
            }
        }
    }
    
    updateChart() {
        if (!this.chart || !this.candleSeries) return;
        try {
            this.candleSeries.setData(this.candleData);
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
            const volData = this.candleData.map(d => ({
                time: d.time,
                value: d.volume,
                color: d.close >= d.open ? 'rgba(38, 166, 154, 0.5)' : 'rgba(239, 83, 80, 0.5)'
            }));
            this.volumeSeries.setData(volData);
            const markers = [];
            this.signals.forEach(sig => {
                if (sig.type === 'BUY') {
                    markers.push({
                        time: sig.time,
                        position: 'belowBar',
                        color: '#26a69a',
                        shape: 'arrowUp',
                        text: 'BUY'
                    });
                } else if (sig.type === 'SELL') {
                    markers.push({
                        time: sig.time,
                        position: 'aboveBar',
                        color: '#ef5350',
                        shape: 'arrowDown',
                        text: 'SELL'
                    });
                }
            });
            this.candleSeries.setMarkers(markers);
            this.chart.timeScale().fitContent();
        } catch (error) {
            console.error('Error updating chart:', error);
        }
    }
    
    updateUI() {
        try {
            const latest = this.candleData[this.candleData.length - 1];
            const latestSignal = this.signals[this.signals.length - 1];
            const latestRSI = this.indicators.rsi[this.indicators.rsi.length - 1];
            const latestMACD = this.indicators.macd[this.indicators.macd.length - 1];
            const latestStoch = this.indicators.stochastic[this.indicators.stochastic.length - 1];
            
            document.getElementById('currentPrice').textContent = `$${latest.close.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 8})}`;
            
            const first = this.candleData[0];
            const changePercent = ((latest.close - first.close) / first.close * 100);
            const changeEl = document.getElementById('priceChange');
            changeEl.textContent = `${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(2)}%`;
            changeEl.className = `value ${changePercent >= 0 ? 'bullish' : 'bearish'}`;
            
            if (latestSignal) {
                const sigEl = document.getElementById('currentSignal');
                sigEl.textContent = latestSignal.type;
                sigEl.className = `signal-badge signal-${latestSignal.type.toLowerCase()}`;
            }
            
            const ema9 = this.indicators.ema9[this.indicators.ema9.length - 1];
            const ema21 = this.indicators.ema21[this.indicators.ema21.length - 1];
            const sma50 = this.indicators.sma50[this.indicators.sma50.length - 1];
            
            let trend = 'Neutral';
            let trendClass = 'neutral';
            if (ema9 && ema21 && sma50) {
                if (ema9 > ema21 && ema21 > sma50) {
                    trend = 'Strong Uptrend';
                    trendClass = 'bullish';
                } else if (ema9 < ema21 && ema21 < sma50) {
                    trend = 'Strong Downtrend';
                    trendClass = 'bearish';
                } else if (ema9 > ema21) {
                    trend = 'Weak Uptrend';
                    trendClass = 'bullish';
                } else {
                    trend = 'Weak Downtrend';
                    trendClass = 'bearish';
                }
            }
            
            const trendEl = document.getElementById('macroTrend');
            trendEl.textContent = trend;
            trendEl.className = `value ${trendClass}`;
            
            let strength = 'Weak';
            if (latestSignal && latestSignal.strength > 5) strength = 'Strong';
            else if (latestSignal && latestSignal.strength > 3) strength = 'Medium';
            document.getElementById('trendStrength').textContent = strength;
            
            if (latestMACD && latestMACD.histogram !== null) {
                const macdEl = document.getElementById('macdValue');
                const macdText = latestMACD.histogram > 0 ? 'Bullish' : 'Bearish';
                const macdClass = latestMACD.histogram > 0 ? 'bullish' : 'bearish';
                macdEl.textContent = `${macdText} (${latestMACD.histogram.toFixed(4)})`;
                macdEl.className = `value ${macdClass}`;
            }
            
            if (latestRSI !== null) {
                const rsiEl = document.getElementById('rsiValue');
                let rsiClass = 'neutral';
                if (latestRSI > 70) rsiClass = 'bearish';
                else if (latestRSI < 30) rsiClass = 'bullish';
                rsiEl.textContent = latestRSI.toFixed(2);
                rsiEl.className = `value ${rsiClass}`;
            }
            
            const momEl = document.getElementById('momentumStatus');
            let mom = 'Neutral';
            let momClass = 'neutral';
            if (latestMACD && latestRSI !== null) {
                if (latestMACD.histogram > 0 && latestRSI > 50) {
                    mom = 'Bullish';
                    momClass = 'bullish';
                } else if (latestMACD.histogram < 0 && latestRSI < 50) {
                    mom = 'Bearish';
                    momClass = 'bearish';
                }
            }
            momEl.textContent = mom;
            momEl.className = `value ${momClass}`;
            
            const recentSwings = this.swingPoints.slice(-5);
            if (recentSwings.length > 0) {
                const last = recentSwings[recentSwings.length - 1];
                document.getElementById('marketStructure').textContent = last.structure || 'Building';
                const swingHTML = recentSwings
                    .filter(s => s.structure)
                    .map(s => `<span class="structure-label ${s.structure.toLowerCase()}">${s.structure}</span>`)
                    .join('');
                document.getElementById('swingPoints').innerHTML = swingHTML || '<span style="color: #787b86; font-size: 12px;">Building...</span>';
            }
            
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
            
            if (latestStoch && latestStoch.k !== null) {
                document.getElementById('stochValue').textContent = latestStoch.k.toFixed(2);
            }
            
            const recentVols = this.candleData.slice(-20).map(d => d.volume);
            const avgVol = recentVols.reduce((a, b) => a + b) / recentVols.length;
            const currVol = latest.volume;
            
            const volEl = document.getElementById('volumeTrend');
            if (currVol > avgVol * 1.5) {
                volEl.textContent = 'High';
                volEl.className = 'value bullish';
            } else if (currVol < avgVol * 0.5) {
                volEl.textContent = 'Low';
                volEl.className = 'value bearish';
            } else {
                volEl.textContent = 'Normal';
                volEl.className = 'value neutral';
            }
            
            const recent20 = this.candleData.slice(-20);
            const bullish = recent20.filter(d => d.close > d.open).length;
            const bearish = recent20.filter(d => d.close < d.open).length;
            
            document.getElementById('buyPressure').textContent = `${(bullish / 20 * 100).toFixed(0)}%`;
            document.getElementById('sellPressure').textContent = `${(bearish / 20 * 100).toFixed(0)}%`;
            
            const recentDiv = this.divergences.slice(-5);
            if (recentDiv.length > 0) {
                const divHTML = recentDiv.map(div => {
                    const divClass = div.type.includes('Bullish') ? 'div-bullish' : 'div-bearish';
                    const date = new Date(div.time * 1000).toLocaleDateString();
                    return `
                        <div class="divergence-item ${divClass}">
                            <strong>${div.type}</strong><br>
                            <small>${div.indicator} - ${date}</small>
                        </div>
                    `;
                }).join('');
                document.getElementById('divergenceList').innerHTML = divHTML;
            } else {
                document.getElementById('divergenceList').innerHTML = 
                    '<div style="font-size: 12px; color: #787b86;">No divergences found</div>';
            }
        } catch (error) {
            console.error('Error updating UI:', error);
        }
    }
    
    connectWebSocket() {
        if (this.ws) {
            this.ws.close();
        }
        const stream = `${this.symbol.toLowerCase()}@kline_${this.interval}`;
        this.ws = new WebSocket(`wss://stream.binance.com:9443/ws/${stream}`);
        this.ws.onopen = () => {
            console.log('WebSocket connected');
        };
        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                const k = data.k;
                const candle = {
                    time: Math.floor(k.t / 1000),
                    open: parseFloat(k.o),
                    high: parseFloat(k.h),
                    low: parseFloat(k.l),
                    close: parseFloat(k.c),
                    volume: parseFloat(k.v)
                };
                if (this.candleData.length > 0 && 
                    this.candleData[this.candleData.length - 1].time === candle.time) {
                    this.candleData[this.candleData.length - 1] = candle;
                } else {
                    this.candleData.push(candle);
                    if (this.candleData.length > 500) {
                        this.candleData.shift();
                    }
                }
                this.candleSeries.update(candle);
                document.getElementById('currentPrice').textContent = 
                    `$${candle.close.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 8})}`;
                if (k.x) {
                    console.log('Candle closed, recalculating...');
                    this.calculateAllIndicators();
                    this.detectSignals();
                    this.updateChart();
                    this.updateUI();
                }
            } catch (error) {
                console.error('WebSocket message error:', error);
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
        console.log('Restarting...');
        if (this.ws) {
            this.ws.close();
        }
        this.candleData = [];
        this.signals = [];
        this.divergences = [];
        this.swingPoints = [];
        this.loadHistoricalData();
    }
    
    showError(message) {
        const errorEl = document.getElementById('errorMessage');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        setTimeout(() => {
            errorEl.style.display = 'none';
        }, 5000);
    }
}

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.tradingApp = new TradingPlatform();
    });
} else {
    window.tradingApp = new TradingPlatform();
}
    </script>
</body>
</html>
