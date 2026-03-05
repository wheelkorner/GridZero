import React, { useState, useEffect } from 'react';
import StatsBar from './StatsBar';
import Console from './Console';
import RemoteTerminal from './RemoteTerminal';
import TextEditor from './TextEditor';

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
    const [localCwd, setLocalCwd] = useState('/home/operator');

    const [activeFile, setActiveFile] = useState(null);

    const addLog = (text, type = "normal") => {
        setLogs(prev => [...prev.slice(-100), { id: Date.now() + Math.random(), text, type }]);
    };

    const addRemoteLog = (text, type = "normal") => {
        setRemoteLogs(prev => [...prev.slice(-20), { text, type }]);
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

    // Online Status and Refresh logic
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
                        addLog("SISTEMA: AÇÃO CONCLUÍDA. CONEXÃO REMOTA ENCERRADA.", "system");
                        setRemoteLogs([]);
                        setRemoteCwd('/');
                    }
                }
            } catch (error) {
                console.error("Online status update failed");
            }
        };

        // Update last_seen_at and sync every 30s
        timer = setInterval(refreshUser, 30000);

        return () => clearInterval(timer);
    }, [user.id]);

    const processRemoteCommand = (input) => {
        const [cmd, ...args] = input.trim().split(/\s+/);
        addRemoteLog(`remote@target:~$ ${input}`, "command");

        const nodeId = user.pending_action?.node_id;
        const vfs = VFS_DATA[nodeId] || VFS_DATA[1];

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
                    addRemoteLog(`cd: ${path}: No such directory`, "error");
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
            case 'help':
                addRemoteLog("AVAILABLE: ls, cd, cp [file], rm [file], help");
                break;
            default:
                addRemoteLog(`command not found: ${cmd}`, "error");
        }
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

    const processCommand = async (input) => {
        const [cmd, ...args] = input.trim().split(/\s+/);
        if (!cmd) return;
        addLog(`operator@gridzero:~$ ${input}`, "command");

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
                    const nodesRes = await globalThis.axios.get('/api/nodes');
                    nodesRes.data.forEach(node => addLog(`NODE [${node.id}] - ${node.name} (DIFF: ${node.difficulty})`));
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
                case 'wallet': addLog(`CRÉDITOS: ${user.stats?.credits || 0} CR`); break;
                case 'clear': setLogs([]); break;
                case 'help':
                    addLog("SISTEMA: status, scan, hack [id], probe [id], wallet, clear, help");
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

    return (
        <div className="h-screen w-screen flex flex-col p-4 border-2 border-neon-green m-0 box-border relative font-mono text-neon-green bg-black selection:bg-neon-green selection:text-black">
            <StatsBar user={user} />

            <main className="flex-1 mt-4 border border-neon-green/30 p-4 relative overflow-hidden bg-black/50 shadow-[inset_0_0_20px_rgba(57,255,20,0.05)]">
                <div className="absolute top-0 right-0 p-2 text-[10px] opacity-40 flex items-center gap-2">
                    {user.role === 'admin' && <span className="text-red-500 animate-pulse font-bold">[ROOT_ACCESS ACTIVE]</span>}
                    <span>SECURE_TERMINAL_V1.1 | CWD: {localCwd}</span>
                </div>

                <Console logs={logs} onCommand={processCommand} />

                {user.pending_action && (
                    <RemoteTerminal
                        node={{ id: user.pending_action.node_id, name: `TARGET_${user.pending_action.node_id}` }}
                        action={user.pending_action}
                        logs={remoteLogs}
                        onCommand={processRemoteCommand}
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
        </div>
    );
};

export default Dashboard;
