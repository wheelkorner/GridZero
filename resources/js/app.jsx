import './bootstrap';
import '../css/app.css';

import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import Login from './components/Login';
import Dashboard from './components/Dashboard';

function App() {
    const [user, setUser] = useState(null);
    const [checking, setChecking] = useState(true);

    useEffect(() => {
        globalThis.axios.get('/api/user')
            .then(res => setUser(res.data))
            .catch(() => { }) // not logged in
            .finally(() => setChecking(false));
    }, []);

    const handleLogout = async () => {
        try {
            await globalThis.axios.post('/api/logout');
        } catch (_) { }
        setUser(null);
    };

    if (checking) {
        return (
            <div className="h-screen w-screen flex items-center justify-center bg-black font-mono text-sm" style={{ color: '#39FF14' }}>
                <span className="animate-pulse">INICIALIZANDO GRID_ZERO_NET...</span>
            </div>
        );
    }

    return user
        ? <Dashboard user={user} setUser={setUser} onLogout={handleLogout} />
        : <Login onLogin={setUser} />;
}

const root = document.getElementById('app');
if (root) {
    ReactDOM.createRoot(root).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}
