const fs = require('fs');
let content = fs.readFileSync('resources/views/pos/index.blade.php', 'utf8');
let match = content.match(/function posApp\(\) \{([\s\S]*)\}/);
if (match) {
    let code = "function posApp() {" + match[1] + "}";
    // remove blade syntax for testing
    code = code.replace(/\{\{.*?\}\}/g, '"{blade}"');
    code = code.replace(/@foreach.*?@endforeach/gs, '');
    try {
        new Function(code);
        console.log("No syntax errors");
    } catch (e) {
        console.error("Syntax Error: " + e.message);
    }
}
