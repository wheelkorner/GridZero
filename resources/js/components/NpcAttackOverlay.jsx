import React, { useState, useEffect, useRef } from 'react';

/**
 * NpcAttackOverlay
 * Displayed when an NPC exploits the player's vulnerability window.
 * Blocks the terminal for 30 seconds while playing a scripted attack sequence.
 *
 * Props:
 *  attacker   { username }  - The NPC performing the attack
 *  files      string[]      - Files in player root to "delete"
 *  onComplete (xpLost)      - Called when the 30s attack finishes
 */
const NpcAttackOverlay = ({ attacker, files, onComplete }) => {
    const DURATION = 30; // seconds the attack lasts

    const [lines, setLines] = useState([]);
    const [timeLeft, setTimeLeft] = useState(DURATION);
    const [phase, setPhase] = useState('breach'); // breach → infiltrate → destroy → exfil → done
    const termRef = useRef(null);
    const intervalRef = useRef(null);

    // Each attack has a scripted sequence of log lines
    const targetFile = files?.[0] || 'notes.txt';
    const script = [
        { delay: 0, text: `[BREACH] CONEXÃO NÃO AUTORIZADA DETECTADA`, type: 'error' },
        { delay: 600, text: `[*] ORIGEM: ${attacker?.username || 'UNKNOWN'}.gridzero.net`, type: 'system' },
        { delay: 1200, text: `[*] ESTABELECENDO CANAL SEGURO...`, type: 'normal' },
        { delay: 2000, text: `[*] CONTORNANDO FIREWALL...`, type: 'normal' },
        { delay: 2800, text: `[+] ACESSO ROOT CONCEDIDO.`, type: 'system' },
        { delay: 3400, text: `$ whoami`, type: 'cmd' },
        { delay: 4000, text: `root`, type: 'normal' },
        { delay: 4600, text: `$ cd /home/operator`, type: 'cmd' },
        { delay: 5200, text: `$ ls`, type: 'cmd' },
        { delay: 5800, text: files?.join('  ') || 'notes.txt  downloads', type: 'normal' },
        { delay: 6600, text: `$ cat .credentials`, type: 'cmd' },
        { delay: 7200, text: `USER=operator`, type: 'normal' },
        { delay: 7600, text: `HASH=$6$e3a9b1c2$d8f7...`, type: 'normal' },
        { delay: 8200, text: `[+] CREDENCIAIS CAPTURADAS.`, type: 'system' },
        { delay: 9000, text: `$ rm -rf ${targetFile}`, type: 'cmd' },
        { delay: 9800, text: `[+] ARQUIVO DELETADO: ${targetFile}`, type: 'error' },
        { delay: 10600, text: `$ python3 drain_cpu.py --target operator`, type: 'cmd' },
        { delay: 11400, text: `[*] CPU ATTACK IN PROGRESS...`, type: 'normal' },
        { delay: 12200, text: `[*] DRENINANDO RECURSOS DO SISTEMA...`, type: 'normal' },
        { delay: 13000, text: `[+] XP REDUZIDO. DANO APLICADO.`, type: 'error' },
        { delay: 14000, text: `$ logout`, type: 'cmd' },
        { delay: 14800, text: `[*] CONEXÃO ENCERRADA. RASTROS APAGADOS.`, type: 'system' },
        { delay: 15600, text: `[BREACH] OPERAÇÃO CONCLUÍDA.`, type: 'error' },
    ];

    // Scroll terminal to bottom
    useEffect(() => {
        if (termRef.current) {
            termRef.current.scrollTop = termRef.current.scrollHeight;
        }
    }, [lines]);

    // Play the scripted attack lines
    useEffect(() => {
        const timers = script.map(({ delay, text, type }) =>
            setTimeout(() => {
                setLines(prev => [...prev, { text, type }]);
            }, delay)
        );
        return () => timers.forEach(clearTimeout);
    }, []); // eslint-disable-line

    // Countdown timer
    useEffect(() => {
        intervalRef.current = setInterval(() => {
            setTimeLeft(prev => {
                if (prev <= 1) {
                    clearInterval(intervalRef.current);
                    const xpLost = Math.floor(Math.random() * 41) + 10; // 10-50
                    setTimeout(() => onComplete(xpLost, targetFile), 300);
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(intervalRef.current);
    }, []); // eslint-disable-line

    const progress = ((DURATION - timeLeft) / DURATION) * 100;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/85 backdrop-blur-sm">
            {/* Scanline effect */}
            <div
                className="absolute inset-0 pointer-events-none opacity-10"
                style={{ backgroundImage: 'repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(57,255,20,0.3) 2px, rgba(57,255,20,0.3) 4px)' }}
            />

            <div className="relative w-[600px] max-h-[80vh] flex flex-col border-2 border-red-500 shadow-[0_0_60px_rgba(255,0,0,0.4)] bg-black font-mono">

                {/* Header */}
                <div className="bg-red-900/60 px-4 py-2 border-b border-red-500 flex justify-between items-center">
                    <span className="text-red-400 font-bold text-sm animate-pulse">⚠ SISTEMA COMPROMETIDO</span>
                    <div className="flex items-center gap-3">
                        <span className="text-red-300 text-xs">INVASOR: <strong>{attacker?.username || '???'}</strong></span>
                        <span className={`font-bold text-sm ${timeLeft <= 10 ? 'text-red-500 animate-pulse' : 'text-orange-400'}`}>
                            {timeLeft}s
                        </span>
                    </div>
                </div>

                {/* Fake terminal output */}
                <div
                    ref={termRef}
                    className="flex-1 overflow-y-auto p-4 space-y-1 text-xs bg-black/90 min-h-[300px] max-h-[400px]"
                >
                    {lines.map((line, i) => (
                        <div key={i} className={
                            line.type === 'error' ? 'text-red-400' :
                                line.type === 'system' ? 'text-yellow-400' :
                                    line.type === 'cmd' ? 'text-cyan-400' :
                                        'text-neon-green/80'
                        }>
                            {line.type === 'cmd' && <span className="text-red-500 mr-1">{'>'}</span>}
                            {line.text}
                        </div>
                    ))}
                    {/* Blinking cursor */}
                    <span className="text-red-400 animate-pulse">_</span>
                </div>

                {/* Progress bar — attack duration */}
                <div className="h-1 bg-red-900/40">
                    <div
                        className="h-full bg-red-500 transition-all duration-1000"
                        style={{ width: `${progress}%` }}
                    />
                </div>

                {/* Footer */}
                <div className="px-4 py-2 border-t border-red-500/50 bg-red-900/20 text-[10px] text-red-400/70 flex justify-between">
                    <span>TERMINAL BLOQUEADO DURANTE O ATAQUE</span>
                    <span>INTRUSION_PROTOCOL_v3.1</span>
                </div>
            </div>
        </div>
    );
};

export default NpcAttackOverlay;
