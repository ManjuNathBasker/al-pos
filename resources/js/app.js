import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.formatCurrency = function(amount, config) {
    const cfg = config || window.currencyConfig || { symbol: '₹', decimal_places: 2, symbol_position: 'before' };
    const num = parseFloat(amount) || 0;
    const isNegative = num < 0;
    const absNum = Math.abs(num);
    const decimals = typeof cfg.decimal_places === 'number' ? cfg.decimal_places : 2;
    const formatted = absNum.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    const res = (cfg.symbol_position === 'after') ? `${formatted} ${cfg.symbol}` : `${cfg.symbol}${formatted}`;
    return isNegative ? `-${res}` : res;
};

Alpine.start();
