const fs = require('fs');
const jsdom = require('jsdom');
const { JSDOM } = jsdom;

let html = fs.readFileSync('pos.html', 'utf8');

const dom = new JSDOM(html, { runScripts: "dangerously" });

setTimeout(() => {
    let err = false;
    dom.window.document.querySelectorAll('[x-data]').forEach(el => {
        if (!el.__x) err = true;
    });
    if (err) {
        console.log("Alpine failed to initialize!");
    } else {
        console.log("Alpine initialized.");
    }
}, 1000);
