import React, { useState, useEffect, useRef } from 'react';

const Console = ({ logs, onCommand }) => {
    const [input, setInput] = useState('');
    const [history, setHistory] = useState([]);
    const [historyIndex, setHistoryIndex] = useState(-1);
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
            setHistory(prev => [input, ...prev]);
            setHistoryIndex(-1);
            setInput('');
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (historyIndex < history.length - 1) {
                const newIndex = historyIndex + 1;
                setHistoryIndex(newIndex);
                setInput(history[newIndex]);
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex > 0) {
                const newIndex = historyIndex - 1;
                setHistoryIndex(newIndex);
                setInput(history[newIndex]);
            } else if (historyIndex === 0) {
                setHistoryIndex(-1);
                setInput('');
            }
        }
    };

    return (
        <div className="h-full flex flex-col font-mono">
            <div
                ref={scrollRef}
                className="flex-1 overflow-y-auto mb-4 custom-scrollbar text-sm"
            >
                {logs.map((log) => (
                    <div key={log.id} className={`mb-2 ${log.type === 'system' ? 'text-blue-400' : log.type === 'error' ? 'text-red-500' : ''}`}>
                        <span className="mr-2 font-bold">
                            {log.type === 'command' ? 'operator@gridzero:~$ ' : ':: '}
                        </span>
                        <span className={log.id === logs[logs.length - 1].id ? 'typewriter-effect' : ''}>
                            {log.text}
                        </span>
                    </div>
                ))}
            </div>

            <form onSubmit={handleSubmit} className="flex border-t border-neon-green/30 pt-4">
                <span className="mr-2 font-bold text-neon-green whitespace-nowrap">operator@gridzero:~$</span>
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyDown={handleKeyDown}
                    autoFocus
                    className="bg-transparent border-none outline-none text-neon-green flex-1"
                    autoComplete="off"
                />
            </form>

            <style>{`
                .typewriter-effect {
                    display: inline-block;
                    overflow: hidden;
                    white-space: nowrap;
                    animation: typing 1s steps(40, end);
                }
                @keyframes typing {
                    from { width: 0 }
                    to { width: 100% }
                }
                .custom-scrollbar::-webkit-scrollbar {
                    width: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #39FF14;
                    opacity: 0.3;
                }
            `}</style>
        </div>
    );
};

export default Console;
