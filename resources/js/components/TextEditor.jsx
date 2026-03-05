import React, { useState, useEffect } from 'react';

const TextEditor = ({ fileName, initialContent, onSave, onExit }) => {
    const [content, setContent] = useState(initialContent || '');

    const handleKeyDown = (e) => {
        // Ctrl+O to Save
        if (e.ctrlKey && e.key === 'o') {
            e.preventDefault();
            onSave(content);
        }
        // Ctrl+X to Exit
        if (e.ctrlKey && e.key === 'x') {
            e.preventDefault();
            onExit();
        }
    };

    return (
        <div className="absolute inset-0 bg-black z-[100] flex flex-col font-mono text-sm animate-fade-in">
            <div className="bg-white text-black px-2 py-0.5 flex justify-between items-center text-xs">
                <span>GNU nano 5.4</span>
                <span className="font-bold">{fileName || 'Novo Arquivo'}</span>
                <span>[ EDITANDO ]</span>
            </div>

            <textarea
                value={content}
                onChange={(e) => setContent(e.target.value)}
                onKeyDown={handleKeyDown}
                className="flex-1 bg-black text-white p-4 outline-none resize-none border-none caret-white"
                autoFocus
                spellCheck="false"
            />

            <div className="bg-white/10 text-white p-2 text-[10px] grid grid-cols-4 gap-2 border-t border-white/20">
                <div><span className="bg-white text-black px-1 mr-1">^O</span> Gravar</div>
                <div><span className="bg-white text-black px-1 mr-1">^X</span> Sair</div>
                <div className="col-span-2 text-right opacity-50">LMT: 6 ARQUIVOS</div>
            </div>
        </div>
    );
};

export default TextEditor;
