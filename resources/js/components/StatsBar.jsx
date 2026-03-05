import React from 'react';

const StatsBar = ({ user }) => {
    const formatCpu = (mhz) => {
        return mhz >= 1000 ? `${(mhz / 1000).toFixed(1)} GHz` : `${mhz} MHz`;
    };

    const formatRam = (mb) => {
        return mb >= 1024 ? `${(mb / 1024).toFixed(1)} GB` : `${mb} MB`;
    };

    return (
        <header className="flex justify-between items-center border-b border-neon-green pb-2 bg-black/40 backdrop-blur-sm px-2">
            <div className="flex items-center gap-4">
                <span className="text-xl font-bold text-neon-green [text-shadow:0_0_5px_rgba(57,255,20,0.5)]">[ {user.username} ]</span>
                <span className="text-xs bg-neon-green text-black px-1 font-bold">LVL {user.level}</span>
            </div>

            <div className="flex gap-4 text-[10px] flex-1 justify-center items-center px-4">
                {/* Hardware Stats */}
                <div className="flex gap-4 border-x border-neon-green/30 px-4">
                    <div className="flex flex-col items-center">
                        <span className="opacity-50">CPU</span>
                        <span className="text-white font-mono">{formatCpu(user.cpu || 800)}</span>
                    </div>
                    <div className="flex flex-col items-center">
                        <span className="opacity-50">RAM</span>
                        <span className="text-white font-mono">{formatRam(user.ram || 512)}</span>
                    </div>
                    <div className="flex flex-col items-center min-w-[80px]">
                        <span className="opacity-50">SSD HEALTH</span>
                        <div className="flex items-center gap-1">
                            <span className={`font-mono ${(user.ssd || 100) < 30 ? 'text-red-500 animate-pulse' : 'text-white'}`}>
                                {user.ssd || 100}%
                            </span>
                            <div className="w-12 h-1.5 bg-white/10 border border-white/20">
                                <div
                                    className={`h-full transition-all duration-500 ${(user.ssd || 100) < 30 ? 'bg-red-500' : 'bg-blue-400'}`}
                                    style={{ width: `${user.ssd || 100}%` }}
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Progress Bar (Central) */}
                {user.pending_action && (
                    <div className="flex flex-col items-center w-64">
                        <span className="text-neon-green animate-pulse text-[9px] uppercase tracking-widest">
                            {user.pending_action.type} IN PROGRESS...
                        </span>
                        <div className="w-full h-1.5 bg-neon-green/10 border border-neon-green/40 mt-0.5 relative">
                            <div
                                className="h-full bg-neon-green shadow-[0_0_8px_#39FF14]"
                                style={{ width: `${user.pending_action.progress || 0}%`, transition: 'width 1s linear' }}
                            ></div>
                        </div>
                    </div>
                )}
            </div>

            <div className="flex gap-6 text-sm">
                <div className="flex flex-col items-end">
                    <span className="text-[9px] opacity-50 uppercase tracking-tighter">Energy</span>
                    <div className="flex items-center gap-2">
                        <span className="text-xs text-white font-mono">{user.energy_points}/100</span>
                        <div className="w-20 h-1 bg-neon-green/20 border border-neon-green/30">
                            <div className="h-full bg-neon-green shadow-[0_0_5px_#39FF14]" style={{ width: `${user.energy_points}%` }}></div>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col items-end">
                    <span className="text-[9px] opacity-50 uppercase tracking-tighter">Credits</span>
                    <span className="text-white font-bold text-xs">{user.stats?.credits || 0} CR</span>
                </div>
            </div>
        </header>
    );
};

export default StatsBar;
