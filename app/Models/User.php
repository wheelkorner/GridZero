<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Interfaces\Interactable;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $level
 * @property int $cpu
 * @property int $ram
 * @property int $ssd
 * @property int $energy_points
 * @property \Carbon\Carbon|null $last_energy_update
 * @property int $reputation_score
 * @property string|null $last_seen_ip
 * @property array|null $stats
 * @property string $role
 * @property bool $is_npc
 * @property \Carbon\Carbon|null $vulnerable_until
 * @property \Carbon\Carbon|null $last_seen_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Action> $actions
 * @property-read \App\Models\Action|null $pending_action
 */
class User extends Authenticatable implements Interactable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'level',
        'cpu',
        'ram',
        'ssd',
        'energy_points',
        'last_energy_update',
        'reputation_score',
        'stats',
        'role',
        'is_npc',
        'vulnerable_until',
        'last_seen_at',
        'last_seen_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_energy_update' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_npc' => 'boolean',
            'vulnerable_until' => 'datetime',
            'stats' => 'array',
            'password' => 'hashed',
            'last_seen_ip' => 'string',
            'reputation_score' => 'integer',
            'level' => 'integer',
        ];
    }

    protected $appends = ['pending_action'];

    /**
     * Get the actions for the user.
     *
     * @return HasMany<Action, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public const DEFAULT_VFS = [
        '/' => ['type' => 'dir', 'children' => ['bin', 'home', 'tmp']],
        '/bin' => ['type' => 'dir', 'children' => ['sh', 'ls', 'cd', 'cat', 'nano', 'tree', 'rmdir']],
        '/home' => ['type' => 'dir', 'children' => ['operator']],
        '/home/operator' => ['type' => 'dir', 'children' => ['downloads', 'notes.txt']],
        '/home/operator/downloads' => ['type' => 'dir', 'children' => []],
        '/tmp' => ['type' => 'dir', 'children' => []],
        '/home/operator/notes.txt' => ['type' => 'file', 'content' => 'Início do GridZero OS. Grid: ativo. Observando...'],
        '/bin/sh' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/ls' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/cd' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/cat' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/nano' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/tree' => ['type' => 'file', 'content' => 'binary_data'],
        '/bin/rmdir' => ['type' => 'file', 'content' => 'binary_data'],
    ];

    /**
     * Accessor for the current pending action.
     *
     * @return Action|null
     */
    public function getPendingActionAttribute()
    {
        return $this->actions()->where('status', 'pending')->first();
    }

    /**
     * Interface Interactable: Retorna a dificuldade baseada no nível.
     */
    public function getDifficulty(): int
    {
        return (int) floor($this->level / 10) ?: 1;
    }

    /**
     * Generates or retrieves the VFS for this user.
     */
    public function generateVfs(): array
    {
        if (!$this->is_npc) {
            return self::DEFAULT_VFS;
        }

        $u = $this->username;
        $lvl = $this->level;
        $isElite = $lvl >= 100;

        // Base VFS
        $vfs = [
            '/' => ['type' => 'dir', 'children' => ['home', 'etc', 'logs', 'root']],
            '/root' => ['type' => 'dir', 'children' => ['tools']],
            '/root/tools' => ['type' => 'dir', 'children' => ['exploit.sh', 'scanner.py']],
            '/home' => ['type' => 'dir', 'children' => [$u]],
            "/home/{$u}" => ['type' => 'dir', 'children' => ['notes.txt']],
            '/etc' => ['type' => 'dir', 'children' => ['shadow', 'hostname']],
            '/logs' => ['type' => 'dir', 'children' => ['access.log']],
        ];

        // Standard content
        $vfs['/etc/hostname'] = ['type' => 'file', 'content' => "{$u}-node.gridzero.net"];
        $vfs['/logs/access.log'] = ['type' => 'file', 'content' => "[BREACH_DETECTED] Unauthorized access from unknown sector."];
        $vfs["/home/{$u}/notes.txt"] = ['type' => 'file', 'content' => "Status: Active. Grid coordination: {$lvl}X."];

        if ($isElite) {
            // Advanced Elite Logic
            $vfs['/root']['children'][] = '.sys';
            $vfs['/root/.sys'] = ['type' => 'dir', 'children' => ['prize_hash.db', 'core.daemon']];

            $prizeHash = hash('sha256', "GZ_PRIZE_SALT_{$u}_{$lvl}");
            $vfs['/root/.sys/prize_hash.db'] = [
                'type' => 'file',
                'content' => "VALIDATION_TOKEN::{$prizeHash}\n[SECURE_STORAGE]::TRANSMIT TO ADMIN FOR REWARD"
            ];
            $vfs['/root/.sys/core.daemon'] = ['type' => 'file', 'content' => 'BINARY_STREAM::' . bin2hex(random_bytes(16))];

            switch ($u) {
                case 'CipherX':
                    $vfs['/'] = ['type' => 'dir', 'children' => ['home', 'etc', 'logs', 'root', 'vault']];
                    $vfs['/vault'] = ['type' => 'dir', 'children' => ['keys.db', 'encrypted_manifest.txt']];
                    $vfs['/vault/keys.db'] = ['type' => 'file', 'content' => 'AES_256_PRIVATE_KEYS_PROTECTED'];
                    break;
                case 'VoidGhost':
                    $vfs['/home']['children'][] = '.ghost';
                    $vfs['/home/.ghost'] = ['type' => 'dir', 'children' => ['stealth_protocol.sh']];
                    $vfs['/home/.ghost/stealth_protocol.sh'] = ['type' => 'file', 'content' => '# Deep stealth sequence'];
                    break;
                case 'KernelPanic':
                    $vfs['/etc']['children'][] = 'sysctl.conf';
                    $vfs['/etc/sysctl.conf'] = ['type' => 'file', 'content' => "kernel.panic = 1\nkernel.panic_on_oops = 1"];
                    $vfs['/logs/access.log']['content'] .= "\nWARNING: STACK_TRACE_DUMP_INITIATED";
                    break;
                case 'NullPtr':
                    $vfs['/root/tools']['children'][] = 'overflow.c';
                    $vfs['/root/tools/overflow.c'] = ['type' => 'file', 'content' => 'void main() { char b[8]; strcpy(b, "AAAA..."); }'];
                    break;
                default:
                    $vfs['/root']['children'][] = '.vault';
                    $vfs['/root/.vault'] = ['type' => 'dir', 'children' => ['elite_manifest.txt']];
                    $vfs['/root/.vault/elite_manifest.txt'] = ['type' => 'file', 'content' => "SECURE_UNIT_ID: {$u}"];
                    break;
            }
            $vfs['/root/tools/exploit.sh']['content'] = "# ELITE VERSION v{$lvl}\n# Target optimization: MAX\nexec /bin/payload";
        } else {
            $vfs['/root/credentials.db'] = ['type' => 'file', 'content' => "USER={$u}\nLEVEL={$lvl}\nKEY=" . strtoupper(substr(md5($u), 0, 16))];
            $vfs['/root']['children'][] = 'credentials.db';
            $vfs['/root/tools/exploit.sh']['content'] = "#!/bin/bash\n# {$u} framework\necho 'Bypass...'";
            $vfs['/root/tools/scanner.py'] = ['type' => 'file', 'content' => 'import socket; # scanner'];
        }

        return $vfs;
    }
}
