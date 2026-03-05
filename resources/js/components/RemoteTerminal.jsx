import React, { useState, useEffect, useRef } from 'react';

const RemoteTerminal = ({ node, action, onCommand, logs, candidates, cwd, sessionTimeLeft }) => {
    const [input, setInput] = useState('');
    const [completions, setCompletions] = useState([]);
    const [completionIndex, setCompletionIndex] = useState(-1);
    const [originalLine, setOriginalLine] = useState('');
    const scrollRef = useRef(null);

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    }, [logs]);

    // Format seconds as M:SS
    const formatTime = (s) => {
        if (s == null) return null;
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${sec.toString().padStart(2, '0')}`;
    };

    const timeStr = formatTime(sessionTimeLeft);
    const timeCritical = sessionTimeLeft !== null && sessionTimeLeft <= 30;

    const handleSubmit = (e) => {
        e.preventDefault();
        if (input.trim()) {
            onCommand(input);
            setInput('');
            setCompletions([]);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();

            const words = input.split(/\s+/);
            const lastWord = words[words.length - 1];

            if (completions.length > 0 && input.endsWith(completions[completionIndex])) {
                const nextIdx = (completionIndex + 1) % completions.length;
                const newWords = [...originalLine.split(/\s+/)];
                newWords[newWords.length - 1] = completions[nextIdx];
                setInput(newWords.join(' '));
                setCompletionIndex(nextIdx);
                return;
            }

            let candidatesList = [];
            if (words.length === 1) {
                candidatesList = (candidates?.system || []).filter(c => c.startsWith(lastWord));
            } else {
                candidatesList = (candidates?.files || []).filter(f => f.startsWith(lastWord));
            }

            if (candidatesList.length > 0) {
                setCompletions(candidatesList);
                setCompletionIndex(0);
                setOriginalLine(input);

                const newWords = [...words];
                newWords[newWords.length - 1] = candidatesList[0];
                setInput(newWords.join(' '));
            }
        } else {
            if (completions.length > 0) setCompletions([]);
        }
    };

    return (
        <div className="absolute bottom-4 right-4 w-96 h-64 bg-black/90 border border-neon-green/50 flex flex-col font-mono text-xs shadow-[0_0_20px_rgba(57,255,20,0.2)] z-50 overflow-hidden animate-fade-in-up">
            <div className="bg-neon-green/10 px-2 py-1 border-b border-neon-green/30 flex justify-between items-center">
                <span className="text-neon-green font-bold">REMOTE_CONNECTION: {node?.name || 'LINKING...'}</span>
                <div className="flex items-center gap-3 text-[10px] opacity-80">
                    {timeStr && (
                        <span className={`font-bold ${timeCritical ? 'text-red-500 animate-pulse' : 'text-yellow-400'}`}>
                            ⏱ {timeStr}
                        </span>
                    )}
                    <span className="opacity-70">TARGET_ID: {node?.id}</span>
                </div>
            </div>

            <div
                ref={scrollRef}
                className="flex-1 overflow-y-auto p-2 space-y-1 scrollbar-thin scrollbar-thumb-neon-green/30"
            >
                {logs.map((log, i) => (
                    <div key={i} className={`${log.type === 'error' ? 'text-red-500' : log.type === 'command' ? 'text-blue-400' : 'text-neon-green'}`}>
                        {log.type === 'command' && <span className="mr-1">remote@target:{log.metadata?.cwd || '/'}$</span>}
                        {log.text}
                    </div>
                ))}
            </div>

            <form onSubmit={handleSubmit} className="p-2 border-t border-neon-green/30 bg-black">
                <div className="flex">
                    <span className="text-neon-green mr-2">remote@target:{cwd}$</span>
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={handleKeyDown}
                        className="flex-1 bg-transparent border-none outline-none text-neon-green"
                        autoFocus
                        autoComplete="off"
                    />
                </div>
            </form>
        </div>
    );
};

export default RemoteTerminal;
