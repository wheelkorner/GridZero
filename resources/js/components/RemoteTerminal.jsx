import React, { useState, useEffect, useRef } from 'react';

const RemoteTerminal = ({ node, action, onCommand, logs }) => {
    const [input, setInput] = useState('');
    const scrollRef = useRef(null);

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    }, [logs]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (input.trim()) {
            onCommand(input);
            setInput('');
        }
    };

    return (
        <div className="absolute bottom-4 right-4 w-96 h-64 bg-black/90 border border-neon-green/50 flex flex-col font-mono text-xs shadow-[0_0_20px_rgba(57,255,20,0.2)] z-50 overflow-hidden animate-fade-in-up">
            <div className="bg-neon-green/10 px-2 py-1 border-b border-neon-green/30 flex justify-between items-center">
                <span className="text-neon-green font-bold">REMOTE_CONNECTION: {node?.name || 'LINKING...'}</span>
                <span className="text-[10px] opacity-70">TARGET_ID: {node?.id}</span>
            </div>

            <div
                ref={scrollRef}
                className="flex-1 overflow-y-auto p-2 space-y-1 scrollbar-thin scrollbar-thumb-neon-green/30"
            >
                {logs.map((log, i) => (
                    <div key={i} className={`${log.type === 'error' ? 'text-red-500' : log.type === 'command' ? 'text-blue-400' : 'text-neon-green'}`}>
                        {log.text}
                    </div>
                ))}
            </div>

            <form onSubmit={handleSubmit} className="p-2 border-t border-neon-green/30 bg-black">
                <div className="flex">
                    <span className="text-neon-green mr-2">remote@target:~$</span>
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        className="flex-1 bg-transparent border-none outline-none text-neon-green"
                        autoFocus
                    />
                </div>
            </form>
        </div>
    );
};

export default RemoteTerminal;
