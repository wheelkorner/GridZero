import React, { useState, useEffect, useRef } from 'react';
import StatsBar from './StatsBar';
import Console from './Console';
import RemoteTerminal from './RemoteTerminal';
import TextEditor from './TextEditor';
import NpcAttackOverlay from './NpcAttackOverlay';

// Default Local VFS Structure (Same as before)
const DEFAULT_LOCAL_VFS = {
    '/': { type: 'dir', children: ['bin', 'home', 'tmp'] },
    '/bin': { type: 'dir', children: ['sh', 'ls', 'cd', 'cat', 'nano', 'tree', 'rmdir'] },
    '/home': { type: 'dir', children: ['operator'] },
    '/home/operator': { type: 'dir', children: ['downloads', 'notes.txt'] },
    '/home/operator/downloads': { type: 'dir', children: [] },
    '/tmp': { type: 'dir', children: [] },
    '/home/operator/notes.txt': { type: 'file', content: 'Início do GridZero OS. Grid: ativo. Observando...' },
    '/bin/sh': { type: 'file', content: 'binary_data' },
    '/bin/ls': { type: 'file', content: 'binary_data' },
    '/bin/cd': { type: 'file', content: 'binary_data' },
    '/bin/cat': { type: 'file', content: 'binary_data' },
    '/bin/nano': { type: 'file', content: 'binary_data' },
    '/bin/tree': { type: 'file', content: 'binary_data' },
    '/bin/rmdir': { type: 'file', content: 'binary_data' },
};

// Simulated File Systems for different Nodes (Same as before)
const VFS_DATA = {
    1: {
        '/': { type: 'dir', children: ['etc', 'home', 'logs'] },
        '/etc': { type: 'dir', children: ['config.sys', 'passwords.db'] },
        '/home': { type: 'dir', children: ['admin', 'guest'] },
        '/home/admin': { type: 'dir', children: ['manifest.txt', 'key.pem'] },
        '/logs': { type: 'dir', children: ['access.log', 'errors.log'] },
        '/etc/config.sys': { type: 'file', content: 'SYS_CONFIG_V1' },
        '/etc/passwords.db': { type: 'file', content: 'DB_HASH_ENCRYPTED' },
        '/home/admin/manifest.txt': { type: 'file', content: 'SECRET_PROJECT_ALPHA' },
        '/home/admin/key.pem': { type: 'file', content: 'RSA_PRIVATE_KEY' },
        '/logs/access.log': { type: 'file', content: 'REMOTE_ACCESS_GRANTED_BY_GUEST' }
    },
    2: {
        '/': { type: 'dir', children: ['root', 'transactions', 'vault'] },
        '/vault': { type: 'dir', children: ['encrypted_data.bin', 'ledger.xlsx'] },
        '/vault/ledger.xlsx': { type: 'file', content: 'BANK_RESERVES: $500,000,000' }
    }
};

const Dashboard = ({ user, setUser, onLogout }) => {
    const [logs, setLogs] = useState([
        { id: 1, text: `BEM-VINDO, ${user?.username?.toUpperCase() ?? 'OPERATOR'}.`, type: "system" },
        { id: 2, text: "SESSÃO INICIADA. AGUARDANDO COMANDOS...", type: "system" }
    ]);

    const [remoteLogs, setRemoteLogs] = useState([]);
    const [remoteCwd, setRemoteCwd] = useState('/');
    const [remoteVfs, setRemoteVfs] = useState(null);
    const [remoteHost, setRemoteHost] = useState(null);
    const [sessionTimeLeft, setSessionTimeLeft] = useState(null);
    const sessionTimerRef = useRef(null);
    const [localCwd, setLocalCwd] = useState('/home/operator');
    const [activeFile, setActiveFile] = useState(null);
    const [npcAttack, setNpcAttack] = useState(null); // { attacker, files }
    const [attackNotif, setAttackNotif] = useState(null); // { xpLost, file }
    const attackedRef = useRef(false); // prevent duplicate attacks in same vulnerability window

    const addLog = (text, type = "normal", metadata = {}) => {
        setLogs(prev => [...prev.slice(-100), { id: Date.now() + Math.random(), text, type, metadata }]);
    };

    const addRemoteLog = (text, type = "normal", metadata = {}) => {
        setRemoteLogs(prev => [...prev.slice(-20), { text, type, metadata }]);
    };

    const getLocalVFS = () => {
        return user.stats?.vfs || DEFAULT_LOCAL_VFS;
    };

    const saveLocalVFS = async (newVFS) => {
        try {
            const updatedStats = { ...user.stats, vfs: newVFS };
            await globalThis.axios.post('/api/user/update-stats', { stats: updatedStats });
            setUser(prev => ({ ...prev, stats: updatedStats }));
        } catch (error) {
            console.error("Failed to persist VFS:", error);
        }
    };

    // Online Status and Refresh logic + NPC attack trigger
    useEffect(() => {
        let timer;
        const refreshUser = async () => {
            try {
                const res = await globalThis.axios.get('/api/user');
                setUser(res.data);

                if (res.data.pending_action) {
                    const now = new Date().getTime();
                    const start = new Date(res.data.pending_action.started_at).getTime();
                    const end = new Date(res.data.pending_action.ends_at).getTime();
                    const total = end - start;
                    const elapsed = now - start;
                    const progress = Math.min(100, Math.max(0, (elapsed / total) * 100));

                    if (progress >= 100) {
                        // Clear pending_action from state immediately so this only fires once
                        setUser(prev => ({ ...prev, pending_action: null }));
                        setRemoteLogs([]);
                        setRemoteCwd('/');
                        addLog("SISTEMA: AÇÃO CONCLUÍDA. CONEXÃO REMOTA ENCERRADA.", "system");
                    }
                }

                // NPC attack trigger: if player is vulnerable, maybe an NPC exploits it
                const vulnUntil = res.data.vulnerable_until ? new Date(res.data.vulnerable_until) : null;
                const isVulnerable = vulnUntil && vulnUntil > new Date();

                if (isVulnerable && !attackedRef.current && !npcAttack) {
                    const roll = Math.random();
                    if (roll < 0.45) { // 45% chance per refresh tick
                        attackedRef.current = true;

                        // Firewall check — intercepts the attack and consumes the flag
                        if (res.data.stats?.firewall_active) {
                            addLog('[FIREWALL-X] ATAQUE NPC INTERCEPTADO E BLOQUEADO!', 'system');
                            const cleanStats = { ...res.data.stats, firewall_active: false };
                            globalThis.axios.post('/api/user/update-stats', { stats: cleanStats }).catch(() => { });
                            setUser(prev => ({ ...prev, stats: cleanStats }));
                        } else {
                            // Pick a random NPC from scan
                            try {
                                const scanRes = await globalThis.axios.get('/api/scan');
                                const npcs = scanRes.data.targets || [];
                                const attacker = npcs.length > 0
                                    ? npcs[Math.floor(Math.random() * npcs.length)]
                                    : { hostname: 'GhostProtocol' };
                                const localVFS = res.data.stats?.vfs || {};
                                const rootFiles = localVFS['/home/operator']?.children || ['notes.txt'];
                                setNpcAttack({ attacker: { username: attacker.hostname }, files: rootFiles });
                            } catch {
                                setNpcAttack({ attacker: { username: 'VoidGhost' }, files: ['notes.txt'] });
                            }
                        }
                    }
                }

                // Reset attack guard when vulnerability window closes
                if (!isVulnerable) {
                    attackedRef.current = false;
                }

            } catch (error) {
                console.error("Online status update failed");
            }
        };

        timer = setInterval(refreshUser, 30000);
        return () => clearInterval(timer);
    }, [user.id, npcAttack]);

    const processRemoteCommand = (input) => {
        const [cmd, ...args] = input.trim().split(/\s+/);
        addRemoteLog(input, "command", { cwd: remoteCwd });

        // Use NPC VFS from server if we connected to an NPC via IP, otherwise fall back to VFS_DATA (Node VFS)
        const nodeId = user.pending_action?.node_id;
        const vfs = remoteVfs || VFS_DATA[nodeId] || VFS_DATA[1];

        switch (cmd.toLowerCase()) {
            case 'ls': {
                const node = vfs[remoteCwd];
                if (node && node.type === 'dir') {
                    addRemoteLog(node.children.join('  '));
                }
                break;
            }
            case 'cd': {
                const path = args[0];
                if (!path) {
                    setRemoteCwd('/');
                    return;
                }
                let targetPath = path.startsWith('/') ? path : (remoteCwd === '/' ? `/${path}` : `${remoteCwd}/${path}`);
                if (vfs[targetPath] && vfs[targetPath].type === 'dir') {
                    setRemoteCwd(targetPath);
                } else if (path === '..') {
                    const parts = remoteCwd.split('/').filter(p => p);
                    parts.pop();
                    setRemoteCwd('/' + parts.join('/'));
                } else {
                    addRemoteLog(`cd: ${path}: No such directory`, "error", { cwd: remoteCwd });
                }
                break;
            }
            case 'cp': {
                const file = args[0];
                if (!file) {
                    addRemoteLog("Usage: cp [file]", "error");
                    return;
                }
                const filePath = file.startsWith('/') ? file : (remoteCwd === '/' ? `/${file}` : `${remoteCwd}/${file}`);
                if (vfs[filePath] && vfs[filePath].type === 'file') {
                    addRemoteLog(`Siphoning ${file} to local system...`);
                    const localVFS = getLocalVFS();
                    const fileName = file.split('/').pop();
                    const localPath = `/home/operator/downloads/${fileName}`;

                    if (!localVFS[localPath]) {
                        const updatedVFS = {
                            ...localVFS,
                            [localPath]: { type: 'file', content: vfs[filePath].content },
                            '/home/operator/downloads': {
                                ...localVFS['/home/operator/downloads'],
                                children: [...localVFS['/home/operator/downloads'].children, fileName]
                            }
                        };
                        saveLocalVFS(updatedVFS);
                    }
                    addRemoteLog("DOWNLOAD COMPLETE. FILE PERSISTED IN LOCAL STORAGE.", "system");
                    addLog(`ARQUIVO RECEBIDO: ${file} (DE NODE ${nodeId}) -> /home/operator/downloads`, "system");
                } else {
                    addRemoteLog(`cp: cannot stat '${file}': No such file`, "error");
                }
                break;
            }
            case 'cat': {
                const file = args[0];
                if (!file) { addRemoteLog('Usage: cat [file]', 'error'); break; }
                const catPath = file.startsWith('/') ? file : (remoteCwd === '/' ? `/${file}` : `${remoteCwd}/${file}`);
                if (vfs[catPath] && vfs[catPath].type === 'file') {
                    vfs[catPath].content.split('\n').forEach(line => addRemoteLog(line));
                } else {
                    addRemoteLog(`cat: ${file}: No such file`, 'error');
                }
                break;
            }
            case 'help':
                addRemoteLog("AVAILABLE: ls, cd, cat [file], cp [file], exit, help");
                break;
            case 'exit':
                disconnectRemote(false);
                break;
            default:
                addRemoteLog(`command not found: ${cmd}`, "error");
        }
    };

    // ---- Clean/forced disconnect helpers --------------------------------
    const stopSessionTimer = () => {
        if (sessionTimerRef.current) {
            clearInterval(sessionTimerRef.current);
            sessionTimerRef.current = null;
        }
    };

    const disconnectRemote = (retaliate = false) => {
        stopSessionTimer();
        setRemoteVfs(null);
        setRemoteHost(null);
        setRemoteLogs([]);
        setRemoteCwd('/');
        setSessionTimeLeft(null);
        // Also clear node-based hack sessions
        setUser(prev => ({ ...prev, pending_action: null }));

        if (retaliate) {
            // NPC counter-attack: drain energy and open attacker's vulnerability window
            const dmg = Math.floor(Math.random() * 21) + 10; // 10-30 energy
            addLog("[!] NPC CONTRA-ATAQUE INICIADO.", "error");
            addLog(`[!] SISTEMA: PERDA DE ${dmg} PONTOS DE ENERGIA.`, "error");
            addLog("[!] SUAS PORTAS ESTÃO ABERTAS POR 120 SEGUNDOS.", "error");
            // Update server
            globalThis.axios.post('/api/user/update-stats', {
                stats: { ...user.stats, energy_drain: dmg }
            }).catch(() => { });
            // Also tell the server the attacker is now vulnerable for 2min
            globalThis.axios.patch?.('/api/user/vulnerable', { seconds: 120 }).catch(() => { });
            setUser(prev => ({ ...prev, energy_points: Math.max(0, (prev.energy_points || 100) - dmg) }));
        } else {
            addLog("SISTEMA: SESSÃO REMOTA ENCERRADA LIMPA.", "system");
        }
    };

    // Start the 2-minute session countdown when connecting to an NPC
    const startSessionTimer = (hostname) => {
        const SESSION_SECONDS = 120;
        setSessionTimeLeft(SESSION_SECONDS);
        stopSessionTimer();

        let remaining = SESSION_SECONDS;
        sessionTimerRef.current = setInterval(() => {
            remaining -= 1;
            setSessionTimeLeft(remaining);
            if (remaining <= 30 && remaining > 0 && remaining % 10 === 0) {
                addRemoteLog(`[!] AVISO: ${remaining}s ATÉ EXPULSÃO DO SISTEMA.`, 'error');
            }
            if (remaining <= 0) {
                addLog(`[!] TEMPO ESGOTADO! ${hostname} DETECTOU A INVASÃO!`, "error");
                disconnectRemote(true);
            }
        }, 1000);
    };

    const processLocalCommand = async (input) => {
        const [cmd, ...args] = input.trim().split(/\s+/);
        const localVFS = getLocalVFS();

        switch (cmd.toLowerCase()) {
            case 'ls': {
                const node = localVFS[localCwd];
                if (node && node.type === 'dir') addLog(node.children.join('  '));
                break;
            }
            case 'cd': {
                const path = args[0];
                if (!path) { setLocalCwd('/home/operator'); return; }
                let targetPath = path.startsWith('/') ? path : (localCwd === '/' ? `/${path}` : `${localCwd === '/' ? '' : localCwd}/${path}`);
                if (path === '..') {
                    const parts = localCwd.split('/').filter(p => p);
                    parts.pop();
                    targetPath = '/' + parts.join('/');
                }
                if (localVFS[targetPath] && localVFS[targetPath].type === 'dir') setLocalCwd(targetPath);
                else addLog(`cd: ${path}: No such directory`, "error");
                break;
            }
            case 'pwd': addLog(localCwd); break;
            case 'whoami': addLog(user.username); break;
            case 'cat': {
                const file = args[0];
                if (!file) return addLog("Usage: cat [file]", "error");
                const filePath = file.startsWith('/') ? file : (localCwd === '/' ? `/${file}` : `${localCwd}/${file}`);
                if (localVFS[filePath] && localVFS[filePath].type === 'file') addLog(`[ CONTENT ]: ${localVFS[filePath].content}`);
                else addLog(`cat: ${file}: No such file`, "error");
                break;
            }
            case 'mkdir': {
                const name = args[0];
                if (!name) return addLog("Usage: mkdir [name]", "error");
                const newPath = localCwd === '/' ? `/${name}` : `${localCwd}/${name}`;
                if (localVFS[newPath]) return addLog(`mkdir: File exists`, "error");
                const updatedVFS = {
                    ...localVFS,
                    [newPath]: { type: 'dir', children: [] },
                    [localCwd]: { ...localVFS[localCwd], children: [...localVFS[localCwd].children, name] }
                };
                saveLocalVFS(updatedVFS);
                addLog(`DIRECTORY CREATED: ${name}`);
                break;
            }
            case 'rmdir': {
                const name = args[0];
                if (!name) return addLog("Usage: rmdir [dir]", "error");
                const targetPath = name.startsWith('/') ? name : (localCwd === '/' ? `/${name}` : `${localCwd}/${name}`);
                const node = localVFS[targetPath];
                if (!node || node.type !== 'dir' || node.children.length > 0) return addLog(`rmdir: failed to remove (not found, not dir or not empty)`, "error");
                const newVFS = { ...localVFS };
                delete newVFS[targetPath];
                const parentPath = targetPath.substring(0, targetPath.lastIndexOf('/')) || '/';
                newVFS[parentPath] = { ...newVFS[parentPath], children: newVFS[parentPath].children.filter(c => c !== name.split('/').pop()) };
                saveLocalVFS(newVFS);
                addLog(`DIRECTORY REMOVED: ${name}`);
                break;
            }
            case 'nano': {
                const file = args[0];
                if (!file) return addLog("Usage: nano [file]", "error");
                const filePath = file.startsWith('/') ? file : (localCwd === '/' ? `/${file}` : `${localCwd}/${file}`);
                const isOperatorHome = filePath.startsWith('/home/operator');
                if (isOperatorHome && !localVFS[filePath]) {
                    const operatorDir = localVFS['/home/operator'];
                    const fileCount = operatorDir.children.filter(c => localVFS[`/home/operator/${c}`]?.type === 'file').length;
                    if (fileCount >= 6) return addLog("ERRO: LIMITE DE 6 ARQUIVOS ATINGIDO.", "error");
                }
                setActiveFile({ name: file, path: filePath, content: localVFS[filePath]?.content || '' });
                break;
            }
            case 'tree': {
                const startPath = args[0] || localCwd;
                const path = startPath.startsWith('/') ? startPath : (localCwd === '/' ? `/${startPath}` : `${localCwd}/${startPath}`);
                if (!localVFS[path]) return addLog(`tree: ${startPath}: No such directory`, "error");
                addLog(`ESTRUTURA: ${path}`);
                const buildTree = (currentPath, prefix = "") => {
                    const node = localVFS[currentPath];
                    if (!node || node.type !== 'dir') return;
                    node.children.forEach((childName, index) => {
                        const isLast = index === node.children.length - 1;
                        const childPath = currentPath === '/' ? `/${childName}` : `${currentPath}/${childName}`;
                        const connector = isLast ? "└── " : "├── ";
                        addLog(`${prefix}${connector}${childName}`);
                        if (localVFS[childPath]?.type === 'dir') {
                            const newPrefix = prefix + (isLast ? "    " : "│   ");
                            buildTree(childPath, newPrefix);
                        }
                    });
                };
                buildTree(path);
                break;
            }
            default: return false;
        }
        return true;
    };

    const processAdminCommand = async (input) => {
        const parts = input.trim().split(/\s+/);
        if (parts[0].toLowerCase() !== 'sudo') return false;

        if (user.role !== 'admin') {
            addLog("ERRO: VOCÊ NÃO TEM PRIVILÉGIOS DE ADMINISTRADOR.", "error");
            return true;
        }

        const cmd = parts[1]?.toLowerCase();
        const arg = parts[2];

        try {
            switch (cmd) {
                case 'users': {
                    addLog("--- NETWORK OPERATORS LIST ---");
                    const res = await globalThis.axios.get('/api/admin/users');
                    res.data.forEach(u => {
                        const status = u.is_online ? "[ONLINE]" : "[OFFLINE]";
                        const color = u.is_online ? "text-neon-green" : "opacity-50";
                        addLog(`${status} ${u.username.padEnd(20)} | LVL ${u.level.toString().padEnd(3)} | ROLE: ${u.role.padEnd(8)} | ${u.last_seen}`, u.is_online ? "system" : "normal");
                    });
                    break;
                }
                case 'info': {
                    if (!arg) return addLog("Usage: sudo info [user]", "error");
                    const res = await globalThis.axios.get(`/api/admin/users/${arg}`);
                    const u = res.data;
                    addLog(`--- PLAYER DATA: ${u.username} ---`);
                    addLog(`LVL: ${u.level} | ROLE: ${u.role} | HEALTH: ${u.ssd}%`);
                    addLog(`CPU: ${u.cpu} MHz | RAM: ${u.ram} MB | ENERGY: ${u.energy}/100`);
                    addLog(`CREDITS: ${u.stats?.credits || 0} CR | XP: ${u.stats?.xp || 0}`);
                    addLog(`LAST SEEN: ${u.last_seen}`);
                    break;
                }
                case 'impersonate': {
                    if (!arg) return addLog("Usage: sudo impersonate [user]", "error");
                    addLog(`ATTEMPTING IMPERSONATION OF ${arg}...`);
                    const res = await globalThis.axios.post(`/api/admin/impersonate/${arg}`);
                    setUser(res.data.user);
                    addLog(`SUDO: IDENTIDADE TROCADA PARA ${arg}.`, "system");
                    addLog(`AVISO: VOCÊ ESTÁ AGORA AGINDO COMO ${arg}.`, "error");
                    break;
                }
                default:
                    addLog(`sudo: command not found: ${cmd}`, "error");
            }
        } catch (error) {
            addLog(`SUDO ERROR: ${error.response?.data?.message || error.message}`, "error");
        }
        return true;
    };

    const handleSaveFile = (content) => {
        const localVFS = getLocalVFS();
        const { path, name } = activeFile;
        const isNewFile = !localVFS[path];
        const newVFS = { ...localVFS };
        newVFS[path] = { type: 'file', content: content };
        if (isNewFile) {
            const parentPath = path.substring(0, path.lastIndexOf('/')) || '/';
            newVFS[parentPath] = { ...newVFS[parentPath], children: [...newVFS[parentPath].children, name] };
        }
        saveLocalVFS(newVFS);
        addLog(`SALVO: ${path}`, "system");
    };

    const processCommand = async (input, isRemote = false) => {
        if (isRemote) return; // processRemoteCommand already handled it
        const [cmd, ...args] = input.trim().split(/\s+/);
        if (!cmd) return;
        addLog(input, "command", { cwd: localCwd });

        if (await processAdminCommand(input)) return;
        if (await processLocalCommand(input)) return;

        try {
            switch (cmd.toLowerCase()) {
                case 'logout': await onLogout(); break;
                case 'status': {
                    const userRes = await globalThis.axios.get('/api/user');
                    setUser(userRes.data);
                    const u = userRes.data;
                    const formatCpu = (m), formatRam = (mb) => mb >= 1024 ? `${(mb / 1024).toFixed(1)} GB` : `${mb} MB`;
                    const formatCpuStr = (mhz) => mhz >= 1000 ? `${(mhz / 1000).toFixed(1)} GHz` : `${mhz} MHz`;
                    addLog(`USUÁRIO: ${u.username} | NÍVEL: ${u.level} ${u.role === 'admin' ? '[ROOT]' : ''}`);
                    addLog(`HARDWARE: [ CPU: ${formatCpuStr(u.cpu)} ] [ RAM: ${formatRam(u.ram)} ] [ SSD: ${u.ssd}% ]`);
                    addLog(`ENERGIA: ${u.energy_points}/100 | XP: ${u.stats?.xp || 0}`);
                    break;
                }
                case 'scan': {
                    if (args[0] === '-net') {
                        const netRes = await globalThis.axios.get('/api/scan');
                        if (netRes.data.targets && netRes.data.targets.length > 0) {
                            addLog("[+] INICIANDO VARREDURA DE REDE...", "system");
                            netRes.data.targets.forEach(t => {
                                const vuln = t.ports.includes('OPEN');
                                addLog(`[+] HOST: ${t.hostname.padEnd(22)} | IP: ${t.ip.padEnd(18)} | PORTAS: ${t.ports}`, vuln ? 'system' : 'normal');
                                if (vuln) {
                                    addLog(`[!] CONNECT: connect ${t.ip}  (janela: ${t.vulnerability_window})`, 'error');
                                }
                            });
                        } else {
                            addLog("[-] NENHUM ALVO VULNERÁVEL DETECTADO NA SUB-REDE.", "normal");
                        }
                    } else {
                        const nodesRes = await globalThis.axios.get('/api/nodes');
                        nodesRes.data.forEach(node => addLog(`NODE [${node.id}] - ${node.name} (DIFF: ${node.difficulty})`));
                    }
                    break;
                }
                case 'connect': {
                    if (!args[0]) return addLog("ERRO: IP NECESSÁRIO. Uso: connect <ip>", "error");
                    addLog(`[*] TENTANDO CONEXÃO COM ${args[0]}...`, "system");
                    try {
                        const res = await globalThis.axios.post('/api/connect', { ip: args[0] });
                        const { hostname, ip, vfs } = res.data;
                        setRemoteVfs(vfs);
                        setRemoteHost({ hostname, ip });
                        setRemoteCwd('/');
                        setRemoteLogs([
                            { text: `CONNECTED TO ${hostname} [${ip}]`, type: 'system' },
                            { text: 'ROOT SHELL ESTABLISHED. 2:00 ANTES DO SISTEMA DETECTAR.', type: 'system' },
                            { text: 'USE "exit" PARA SAIR SEM REPRESALIAS.', type: 'error' },
                        ]);
                        addLog(`[+] ACESSO CONCEDIDO: ${hostname} (${ip}). TERMINAL ABERTO.`, "system");
                        startSessionTimer(hostname);
                    } catch (err) {
                        addLog(`[!] FALHA: ${err.response?.data?.message || 'CONEXÃO RECUSADA'}`, "error");
                    }
                    break;
                }
                case 'hack':
                case 'probe': {
                    if (!args[0]) return addLog(`ERRO: ID DO NODE NECESSÁRIO.`, "error");
                    const actionRes = await globalThis.axios.post('/api/actions', { type: cmd, node_id: args[0] });
                    setUser(prev => ({ ...prev, ...actionRes.data.user, pending_action: actionRes.data.data }));
                    setRemoteLogs([{ text: `CONNECTING TO NODE ${args[0]}...`, type: "system" }]);
                    addLog(`SUCESSO: ${actionRes.data.message}`);
                    break;
                }
                case 'wallet': {
                    const bal = user.stats?.credits || 0;
                    const addr = `GZC_${btoa(user.username || 'op').replace(/=/g, '').substring(0, 16).toUpperCase()}`;
                    const brl = (bal * 0.0024).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    addLog('┌─────────────────────────────────────────┐', 'system');
                    addLog('│         ◈  GRIDZERO COIN WALLET  ◈       │', 'system');
                    addLog('├─────────────────────────────────────────┤', 'system');
                    addLog(`│  Endereço : ${addr.padEnd(28)}│`, 'system');
                    addLog(`│  Saldo    : ${String(bal + ' GZC').padEnd(28)}│`, 'system');
                    addLog(`│  Valor    : ~R$ ${String(brl).padEnd(24)}│`, 'system');
                    addLog('└─────────────────────────────────────────┘', 'system');
                    break;
                }

                case 'shop': {
                    const sub = args[0]?.toLowerCase();
                    if (!sub || sub === 'list') {
                        // Display catalog
                        const res = await globalThis.axios.get('/api/shop');
                        addLog('--- GRIDZERO MARKET ---', 'system');
                        res.data.catalog.forEach(item => {
                            const owned = (user.stats?.inventory || []).includes(item.id);
                            addLog(`[${item.id.padEnd(10)}] ${item.name.padEnd(22)} | ${String(item.price).padStart(4)} CR | ${owned ? '[INSTALADO]' : item.description}`, owned ? 'normal' : 'system');
                        });
                        addLog("USO: shop buy <id> | shop use <id> [target] | shop inventory", 'normal');
                    } else if (sub === 'buy') {
                        if (!args[1]) return addLog('USO: shop buy <program_id>', 'error');
                        const res = await globalThis.axios.post('/api/shop/buy', { program_id: args[1] });
                        addLog(`[+] ${res.data.message}`, 'system');
                        addLog(`[*] SALDO RESTANTE: ${res.data.credits} CR`, 'normal');
                        setUser(prev => ({ ...prev, stats: { ...prev.stats, credits: res.data.credits, inventory: res.data.inventory } }));
                    } else if (sub === 'inventory') {
                        const inv = user.stats?.inventory || [];
                        if (inv.length === 0) return addLog('NENHUM PROGRAMA INSTALADO.', 'normal');
                        addLog('--- PROGRAMAS INSTALADOS ---', 'system');
                        inv.forEach(id => addLog(`  > ${id}`, 'system'));
                    } else if (sub === 'use') {
                        if (!args[1]) return addLog('USO: shop use <program_id> [target]', 'error');
                        const res = await globalThis.axios.post('/api/shop/use', { program_id: args[1], target: args[2] || null });
                        addLog(`[+] ${res.data.message}`, 'system');
                        if (res.data.your_credits !== undefined) {
                            addLog(`[*] SEU SALDO: ${res.data.your_credits} CR`, 'system');
                            setUser(prev => ({ ...prev, stats: { ...prev.stats, credits: res.data.your_credits } }));
                        }
                    } else {
                        addLog(`shop: subcomando desconhecido: ${sub}`, 'error');
                    }
                    break;
                }
                case 'clear': setLogs([]); break;
                case 'disconnect': disconnectRemote(false); break;
                case 'help':
                    addLog("SISTEMA: status, scan [-net], connect [ip], disconnect, hack [id], probe [id], wallet, shop, clear, logout");
                    addLog("LOJA:    shop | shop buy <id> | shop use <id> [target] | shop inventory");
                    addLog("ARQUIVOS: ls, cd, pwd, cat, mkdir, rmdir, rm, nano, tree, whoami");
                    if (user.role === 'admin') addLog("ADMIN (SUDO): sudo users, sudo info [user], sudo impersonate [user]");
                    addLog("REMOTO: ls, cd, cp, help");
                    break;
                default: addLog(`COMANDO NÃO RECONHECIDO: ${cmd}`, "error");
            }
        } catch (error) {
            addLog(`ERRO: ${error.response?.data?.message || "CONEXÃO FALHOU"}`, "error");
        }
    };

    const getCandidates = () => {
        const localVFS = getLocalVFS();
        const systemCmds = ['status', 'scan', 'connect', 'disconnect', 'hack', 'probe', 'wallet', 'shop', 'clear', 'logout', 'help', 'ls', 'cd', 'pwd', 'cat', 'mkdir', 'rmdir', 'nano', 'tree', 'whoami'];
        if (user.role === 'admin') systemCmds.push('sudo');

        const currentDir = localVFS[localCwd];
        const files = currentDir ? currentDir.children : [];

        // If sudo is typed, we might want to suggest subcommands
        const adminSubCmds = user.role === 'admin' ? ['users', 'info', 'impersonate'] : [];

        return { system: systemCmds, files, admin: adminSubCmds };
    };

    const handleNpcAttackComplete = (xpLost, deletedFile) => {
        // 1. Delete the file from local VFS
        const localVFS = getLocalVFS();
        const filePath = `/home/operator/${deletedFile}`;
        if (localVFS[filePath]) {
            const newVFS = { ...localVFS };
            delete newVFS[filePath];
            newVFS['/home/operator'] = {
                ...newVFS['/home/operator'],
                children: newVFS['/home/operator'].children.filter(f => f !== deletedFile),
            };
            saveLocalVFS(newVFS);
        }

        // 2. Reduce XP in stats
        const currentXp = user.stats?.xp ?? 0;
        const newXp = Math.max(0, currentXp - xpLost);
        const updatedStats = { ...user.stats, xp: newXp };
        globalThis.axios.post('/api/user/update-stats', { stats: updatedStats }).catch(() => { });
        setUser(prev => ({ ...prev, stats: updatedStats }));

        // 3. Show notification and clear overlay
        setNpcAttack(null);
        setAttackNotif({ xpLost, file: deletedFile, attacker: npcAttack?.attacker?.username });

        // 4. Log to main terminal
        addLog(`[!] ATAQUE CONCLU\u00cdDO: ${npcAttack?.attacker?.username} SAIU DO SEU SISTEMA.`, 'error');
        addLog(`[!] ARQUIVO DELETADO: ${deletedFile} | XP PERDIDO: ${xpLost}`, 'error');

        // Auto-dismiss notification after 8s
        setTimeout(() => setAttackNotif(null), 8000);
    };

    return (
        <div className="h-screen w-screen flex flex-col p-4 border-2 border-neon-green m-0 box-border relative font-mono text-neon-green bg-black selection:bg-neon-green selection:text-black">
            <StatsBar user={user} />

            <main className="flex-1 mt-4 border border-neon-green/30 p-4 relative overflow-hidden bg-black/50 shadow-[inset_0_0_20px_rgba(57,255,20,0.05)]">
                <div className="absolute top-0 right-0 p-2 text-[10px] opacity-40 flex items-center gap-2">
                    {user.role === 'admin' && <span className="text-red-500 animate-pulse font-bold">[ROOT_ACCESS ACTIVE]</span>}
                    <span>SECURE_TERMINAL_V1.1 | CWD: {localCwd}</span>
                </div>

                <Console
                    logs={logs}
                    onCommand={npcAttack ? () => { } : processCommand}
                    candidates={getCandidates()}
                    cwd={localCwd}
                    disabled={!!npcAttack}
                />

                {(user.pending_action || remoteHost) && (
                    <RemoteTerminal
                        node={remoteHost
                            ? { id: remoteHost.ip, name: remoteHost.hostname }
                            : { id: user.pending_action?.node_id, name: `TARGET_${user.pending_action?.node_id}` }
                        }
                        action={user.pending_action || { type: 'connect' }}
                        logs={remoteLogs}
                        onCommand={processRemoteCommand}
                        candidates={{ system: ['ls', 'cd', 'cat', 'cp', 'exit', 'help'], files: (remoteVfs || VFS_DATA[user.pending_action?.node_id])?.[remoteCwd]?.children || [] }}
                        cwd={remoteCwd}
                        sessionTimeLeft={sessionTimeLeft}
                    />
                )}

                {activeFile && (
                    <TextEditor
                        fileName={activeFile.name}
                        initialContent={activeFile.content}
                        onSave={handleSaveFile}
                        onExit={() => setActiveFile(null)}
                    />
                )}
            </main>

            <footer className="mt-4 text-[10px] flex justify-between opacity-50 uppercase tracking-widest">
                <span>GRID_ZERO_NET_OS</span>
                <span>SYSTEM_ROLE: {user.role}</span>
                <span>RSA_4096_VALID</span>
            </footer>

            {/* NPC Attack Overlay — blocks terminal for 30s */}
            {npcAttack && (
                <NpcAttackOverlay
                    attacker={npcAttack.attacker}
                    files={npcAttack.files}
                    onComplete={handleNpcAttackComplete}
                />
            )}

            {/* Post-attack notification toast */}
            {attackNotif && (
                <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[10000] w-[460px] border border-red-500 bg-black/95 shadow-[0_0_30px_rgba(255,0,0,0.5)] p-4 font-mono text-sm animate-fade-in-up">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-red-400 font-bold text-base animate-pulse">⚠ ATAQUE DE CPU DETECTADO</span>
                        <button onClick={() => setAttackNotif(null)} className="text-red-400 hover:text-white">✕</button>
                    </div>
                    <div className="space-y-1 text-xs">
                        <p className="text-orange-300">Invasor: <strong className="text-orange-400">{attackNotif.attacker}</strong></p>
                        <p className="text-red-300">Arquivo deletado: <strong className="text-red-400">{attackNotif.file}</strong></p>
                        <p className="text-red-300">XP perdido: <strong className="text-red-400">-{attackNotif.xpLost} XP</strong></p>
                        <p className="text-yellow-400/70 mt-2 text-[10px]">Use <code>scan -net</code> para rastrear o invasor e contra-atacar.</p>
                    </div>
                    {/* Auto-dismiss bar */}
                    <div className="mt-3 h-0.5 bg-red-900/40">
                        <div className="h-full bg-red-500 animate-[shrink_8s_linear_forwards]" style={{ width: '100%' }} />
                    </div>
                </div>
            )}
        </div>
    );
};

export default Dashboard;
