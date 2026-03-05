import React, { useState, useEffect } from 'react';

const BANNER = [
    '  ██████╗ ██████╗ ██╗██████╗ ███████╗███████╗██████╗  ██████╗  ',
    ' ██╔════╝ ██╔══██╗██║██╔══██╗╚══███╔╝██╔════╝██╔══██╗██╔═══██╗ ',
    ' ██║  ███╗██████╔╝██║██║  ██║  ███╔╝ █████╗  ██████╔╝██║   ██║ ',
    ' ██║   ██║██╔══██╗██║██║  ██║ ███╔╝  ██╔══╝  ██╔══██╗██║   ██║ ',
    ' ╚██████╔╝██║  ██║██║██████╔╝███████╗███████╗██║  ██║╚██████╔╝ ',
    '  ╚═════╝ ╚═╝  ╚═╝╚═╝╚═════╝ ╚══════╝╚══════╝╚═╝  ╚═╝ ╚═════╝  ',
];

export default function Login({ onLogin }) {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [status, setStatus] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [bannerVisible, setBannerVisible] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => setBannerVisible(true), 200);
        return () => clearTimeout(timer);
    }, []);

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');
        setStatus('AUTENTICANDO...');
        setLoading(true);

        try {
            await globalThis.axios.get('/sanctum/csrf-cookie');
            const res = await globalThis.axios.post('/api/login', { username, password });
            setStatus('ACESSO CONCEDIDO. INICIALIZANDO SESSÃO...');

            if (res.data.user.role === 'admin') {
                setTimeout(() => {
                    window.location.href = '/admin';
                }, 800);
            } else {
                setTimeout(() => onLogin(res.data.user), 800);
            }
        } catch (err) {
            setLoading(false);
            setStatus('');
            if (err.response?.status === 422) {
                setError('CREDENCIAIS INVÁLIDAS. ACESSO NEGADO.');
            } else if (err.response?.status === 429) {
                setError('MUITAS TENTATIVAS. AGUARDE E TENTE NOVAMENTE.');
            } else {
                setError('FALHA NA CONEXÃO COM O SERVIDOR.');
            }
        }
    };

    return (
        <div className="h-screen w-screen flex flex-col items-center justify-center bg-black font-mono p-4">
            {/* Scanlines overlay */}
            <div className="pointer-events-none fixed inset-0 z-50" style={{
                background: 'repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.08) 2px, rgba(0,0,0,0.08) 4px)'
            }} />

            {/* ASCII Banner */}
            <div
                className="mb-8 text-center transition-all duration-700"
                style={{ opacity: bannerVisible ? 1 : 0, transform: bannerVisible ? 'translateY(0)' : 'translateY(-20px)' }}
            >
                {BANNER.map((line, i) => (
                    <div key={i} className="text-[10px] sm:text-xs leading-tight whitespace-pre" style={{ color: '#39FF14' }}>
                        {line}
                    </div>
                ))}
                <p className="text-xs mt-3 opacity-60" style={{ color: '#39FF14' }}>
                    SECURE NETWORK ACCESS TERMINAL — v1.0.0
                </p>
            </div>

            {/* Login Box */}
            <div
                className="w-full max-w-md border p-8 transition-all duration-700 delay-300"
                style={{
                    borderColor: '#39FF14',
                    boxShadow: '0 0 30px rgba(57,255,20,0.15), inset 0 0 30px rgba(57,255,20,0.03)',
                    opacity: bannerVisible ? 1 : 0,
                }}
            >
                {/* Header */}
                <div className="flex items-center justify-between mb-6 pb-3 border-b" style={{ borderColor: 'rgba(57,255,20,0.3)' }}>
                    <span className="text-xs" style={{ color: '#39FF14' }}>AUTENTICAÇÃO REQUERIDA</span>
                    <span className="text-xs opacity-50" style={{ color: '#39FF14' }}>
                        <span className="inline-block w-2 h-2 rounded-full mr-1 animate-pulse" style={{ background: '#39FF14' }} />
                        ONLINE
                    </span>
                </div>

                <form onSubmit={handleLogin} className="space-y-5">
                    {/* Username */}
                    <div>
                        <label className="text-xs opacity-70 block mb-1" style={{ color: '#39FF14' }}>
                            IDENTIFICADOR DE REDE
                        </label>
                        <div className="flex items-center border" style={{ borderColor: 'rgba(57,255,20,0.4)' }}>
                            <span className="px-3 text-sm opacity-60" style={{ color: '#39FF14' }}>{'>'}</span>
                            <input
                                type="text"
                                value={username}
                                onChange={e => setUsername(e.target.value)}
                                autoFocus
                                autoComplete="username"
                                disabled={loading}
                                className="flex-1 bg-transparent py-2 pr-3 text-sm outline-none"
                                style={{ color: '#39FF14', caretColor: '#39FF14' }}
                                placeholder="username"
                            />
                        </div>
                    </div>

                    {/* Password */}
                    <div>
                        <label className="text-xs opacity-70 block mb-1" style={{ color: '#39FF14' }}>
                            CHAVE DE ACESSO
                        </label>
                        <div className="flex items-center border" style={{ borderColor: 'rgba(57,255,20,0.4)' }}>
                            <span className="px-3 text-sm opacity-60" style={{ color: '#39FF14' }}>{'>'}</span>
                            <input
                                type="password"
                                value={password}
                                onChange={e => setPassword(e.target.value)}
                                autoComplete="current-password"
                                disabled={loading}
                                className="flex-1 bg-transparent py-2 pr-3 text-sm outline-none"
                                style={{ color: '#39FF14', caretColor: '#39FF14' }}
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    {/* Status / Error */}
                    <div className="h-5 text-xs">
                        {error && (
                            <p className="text-red-500 animate-pulse">{error}</p>
                        )}
                        {status && !error && (
                            <p style={{ color: '#39FF14' }} className="opacity-80">{status}</p>
                        )}
                    </div>

                    {/* Submit */}
                    <button
                        type="submit"
                        disabled={loading || !username || !password}
                        className="w-full py-3 text-sm font-bold tracking-widest transition-all duration-200 border disabled:opacity-30"
                        style={{
                            color: loading ? 'rgba(57,255,20,0.5)' : '#000',
                            background: loading ? 'transparent' : '#39FF14',
                            borderColor: '#39FF14',
                            boxShadow: loading ? 'none' : '0 0 20px rgba(57,255,20,0.4)',
                        }}
                    >
                        {loading ? 'PROCESSANDO...' : 'INICIAR SESSÃO'}
                    </button>
                </form>

                {/* Footer hint */}
                <p className="text-center text-xs mt-6 opacity-30" style={{ color: '#39FF14' }}>
                    ACESSO NÃO AUTORIZADO SERÁ RASTREADO E REPORTADO
                </p>
            </div>

            <p className="mt-6 text-xs opacity-20" style={{ color: '#39FF14' }}>
                GRID_ZERO_NET © 2025 — ENC: RSA_4096
            </p>
        </div>
    );
}
