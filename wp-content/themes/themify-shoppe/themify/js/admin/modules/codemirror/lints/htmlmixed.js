( (CodeMirror, HTMLHint)=> {
    'use strict';
    if (HTMLHint && !HTMLHint.verify) {
        HTMLHint = HTMLHint.default!==undefined?HTMLHint.default:HTMLHint.HTMLHint;
    }
    const newlines=str=> {
        return str.split("\n").length;
    },
    // our JS error parser is extended with the offset argument
    parseErrors=(errors, output, offset)=>{
        for (let i = 0; i < errors.length; ++i) {
            let error = errors[i];
            if (error) {
                if(error.line>0){
                    let start = error.character - 1,
                        end = start + 1;
                    if (error.evidence) {
                        let index = error.evidence.substring(start).search(/.\b/);
                        if (index > -1) {
                            end += index;
                        }
                    }

                    let line = error.line - 1 + offset - 1,
                    // Convert to format expected by validation service
                    hint = {
                        message: error.reason,
                        severity: error.code ? (error.code.startsWith('W') ? 'warning' : 'error') : 'error',
                        from: CodeMirror.Pos(line, start),
                        to: CodeMirror.Pos(line, end)
                    };

                    output.push(hint);
                }
            }
        }
    },
    processHTML=(text, options, found)=> {
        const messages = HTMLHint.verify(text, options);
        for (let i = 0; i < messages.length; i++) {
            let message = messages[i],
             startLine = message.line - 1,
                endLine = message.line - 1,
                startCol = message.col - 1,
                endCol = message.col;
            found.push({
                from: CodeMirror.Pos(startLine, startCol),
                to: CodeMirror.Pos(endLine, endCol),
                message: message.message,
                severity: message.type
            });
        }
    },
    processCSS=(text, options, found)=>{
        const blocks = text.split(/<style[\s\S]*?>|<\/style>/gi);
        for (let j = 1; j < blocks.length; j += 2) {
            let offset = newlines(blocks.slice(0, j).join()),
             results = CSSLint.verify(blocks[j], options),
             messages = results.messages,
             message = null;
            for (let i = 0; i < messages.length; i++) {
                message = messages[i];
                let startLine = offset - 1 + message.line - 1,
                    endLine = offset - 1 + message.line - 1,
                    startCol = message.col - 1,
                    endCol = message.col;
                found.push({
                    from: CodeMirror.Pos(startLine, startCol),
                    to: CodeMirror.Pos(endLine, endCol),
                    message: message.message,
                    severity: message.type
                });
            }
        }
    },
    processJS= (text, options, found)=> {
        const blocks = text.split(/<script[\s\S]*?>|<\/script>/gi);
        for (let j = 1; j < blocks.length; j += 2) {
            if (blocks[j].length > 1) {
                JSHINT(blocks[j], options, options.globals);
                let errors = JSHINT.data().errors;
                if (errors){
                    parseErrors(errors, found, newlines(blocks.slice(0, j).join()));
                }
            }
        }
    };
    CodeMirror.registerHelper('lint', 'html',  (text, options)=>{
        
        if (!options.indent){
            // JSHint error.character actually is a column index, this fixes underlining on lines using tabs for indentation
            options.indent = 1; // JSHint default value is 4
        }
        // external linters may modify the options object, so for example CSSLinter adds options.errors, but then JSLint complains that it is not a valid option
        // let us add an additional layer in case we want to define linter-specific option via options, otherwise take clones of the defualt options object
        const found = [];
        processHTML(text, options.html.options, found);
        processCSS(text, options.css.options, found);
        processJS(text, options.javascript.options, found);

        return found;
    });
})(CodeMirror, HTMLHint);