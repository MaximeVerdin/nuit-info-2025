// Simulateur de terminal Linux interactif
let currentDirectory = '~';
let fileSystem = {
    '~': {
        type: 'directory',
        contents: {
            'Documents': {
                type: 'directory',
                contents: {
                    'mon_fichier.txt': {
                        type: 'file',
                        content: 'Bonjour ! Ceci est un fichier texte.\nVous pouvez lire son contenu avec la commande cat.'
                    }
                }
            },
            'Images': {
                type: 'directory',
                contents: {}
            },
            'bienvenue.txt': {
                type: 'file',
                content: 'Bienvenue dans le simulateur de terminal Linux !\n\nCe fichier vous montre comment utiliser la commande cat pour lire un fichier.'
            }
        }
    }
};

let commandHistory = [];
let historyIndex = -1;

document.addEventListener('DOMContentLoaded', function() {
    const terminalInput = document.getElementById('terminalInput');
    if (terminalInput) {
        terminalInput.addEventListener('keydown', handleKeyPress);
        terminalInput.focus();
    }
});

function handleKeyPress(event) {
    if (event.key === 'Enter') {
        executeCommand();
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        navigateHistory(-1);
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        navigateHistory(1);
    }
}

function navigateHistory(direction) {
    if (commandHistory.length === 0) return;
    
    historyIndex += direction;
    
    if (historyIndex < 0) {
        historyIndex = -1;
        document.getElementById('terminalInput').value = '';
    } else if (historyIndex >= commandHistory.length) {
        historyIndex = commandHistory.length - 1;
    } else {
        document.getElementById('terminalInput').value = commandHistory[historyIndex];
    }
}

function executeCommand() {
    const input = document.getElementById('terminalInput');
    const command = input.value.trim();
    
    if (command === '') {
        addOutputLine('', '');
        input.value = '';
        return;
    }
    
    // Ajouter à l'historique
    commandHistory.push(command);
    historyIndex = commandHistory.length;
    
    // Afficher la commande
    addCommandLine(command);
    
    // Exécuter la commande
    const result = processCommand(command);
    addOutputLine(result.output, result.type);
    
    // Réinitialiser l'input
    input.value = '';
    
    // Scroll vers le bas
    scrollToBottom();
}

function addCommandLine(command) {
    const terminalBody = document.getElementById('terminalBody');
    const line = document.createElement('div');
    line.className = 'terminal-line';
    line.innerHTML = `<span class="terminal-prompt">utilisateur@linux:${currentDirectory}$</span> ${escapeHtml(command)}`;
    
    // Insérer avant le dernier élément (l'input)
    const inputLine = terminalBody.lastElementChild;
    terminalBody.insertBefore(line, inputLine);
}

function addOutputLine(output, type = 'output') {
    const terminalBody = document.getElementById('terminalBody');
    const line = document.createElement('div');
    line.className = 'terminal-line';
    
    let className = 'terminal-output';
    if (type === 'error') {
        className = 'terminal-error';
    } else if (type === 'success') {
        className = 'terminal-success';
    }
    
    line.innerHTML = `<span class="${className}">${escapeHtml(output)}</span>`;
    
    // Insérer avant le dernier élément (l'input)
    const inputLine = terminalBody.lastElementChild;
    terminalBody.insertBefore(line, inputLine);
}

function processCommand(command) {
    const parts = command.split(' ').filter(p => p.length > 0);
    const cmd = parts[0].toLowerCase();
    const args = parts.slice(1);
    
    switch(cmd) {
        case 'help':
            return {
                output: `Commandes disponibles dans ce simulateur :

📁 NAVIGATION
  pwd              - Affiche le répertoire actuel
  ls [dossier]     - Liste les fichiers et dossiers
  cd [dossier]     - Change de répertoire (utilisez 'cd ..' pour remonter)
  cat [fichier]    - Affiche le contenu d'un fichier
  mkdir [nom]      - Crée un nouveau dossier
  touch [nom]      - Crée un nouveau fichier vide

ℹ️  AIDE
  help             - Affiche cette aide
  clear            - Efface l'écran du terminal

💡 Astuce : Essayez 'ls' pour voir les fichiers disponibles, puis 'cd Documents' pour entrer dans le dossier Documents !`,
                type: 'output'
            };
            
        case 'pwd':
            return {
                output: currentDirectory,
                type: 'output'
            };
            
        case 'ls':
            if (args.length > 0) {
                const targetPath = resolvePath(args[0]);
                const target = getPath(targetPath);
                if (!target || target.type !== 'directory') {
                    return {
                        output: `ls: impossible d'accéder à '${args[0]}': Aucun fichier ou dossier de ce type`,
                        type: 'error'
                    };
                }
                return listDirectory(target.contents);
            }
            return listDirectory(getCurrentDirectory().contents);
            
        case 'cd':
            if (args.length === 0) {
                currentDirectory = '~';
                return { output: '', type: 'output' };
            }
            const targetPath = resolvePath(args[0]);
            const target = getPath(targetPath);
            if (!target || target.type !== 'directory') {
                return {
                    output: `cd: ${args[0]}: Aucun fichier ou dossier de ce type`,
                    type: 'error'
                };
            }
            currentDirectory = targetPath;
            return { output: '', type: 'output' };
            
        case 'cat':
            if (args.length === 0) {
                return {
                    output: 'cat: usage: cat [fichier]',
                    type: 'error'
                };
            }
            const filePath = resolvePath(args[0]);
            const file = getPath(filePath);
            if (!file || file.type !== 'file') {
                return {
                    output: `cat: ${args[0]}: Aucun fichier ou dossier de ce type`,
                    type: 'error'
                };
            }
            return {
                output: file.content,
                type: 'output'
            };
            
        case 'mkdir':
            if (args.length === 0) {
                return {
                    output: 'mkdir: usage: mkdir [nom_dossier]',
                    type: 'error'
                };
            }
            const dirName = args[0];
            const currentDir = getCurrentDirectory();
            if (currentDir.contents[dirName]) {
                return {
                    output: `mkdir: impossible de créer le dossier '${dirName}': Le fichier existe déjà`,
                    type: 'error'
                };
            }
            currentDir.contents[dirName] = {
                type: 'directory',
                contents: {}
            };
            return {
                output: '',
                type: 'success'
            };
            
        case 'touch':
            if (args.length === 0) {
                return {
                    output: 'touch: usage: touch [nom_fichier]',
                    type: 'error'
                };
            }
            const fileName = args[0];
            const currentDir2 = getCurrentDirectory();
            if (currentDir2.contents[fileName]) {
                return {
                    output: '',
                    type: 'output'
                };
            }
            currentDir2.contents[fileName] = {
                type: 'file',
                content: ''
            };
            return {
                output: '',
                type: 'success'
            };
            
        case 'clear':
            clearTerminal();
            return { output: '', type: 'output' };
            
        default:
            return {
                output: `${cmd}: commande introuvable\nTapez 'help' pour voir les commandes disponibles.`,
                type: 'error'
            };
    }
}

function getCurrentDirectory() {
    return getPath(currentDirectory);
}

function getPath(path) {
    if (path === '~' || path === '') {
        return fileSystem['~'];
    }
    
    const parts = path.replace(/^~\//, '').split('/').filter(p => p.length > 0);
    let current = fileSystem['~'];
    
    for (const part of parts) {
        if (part === '..') {
            // Pour simplifier, on remonte toujours vers ~
            return fileSystem['~'];
        }
        if (current.contents && current.contents[part]) {
            current = current.contents[part];
        } else {
            return null;
        }
    }
    
    return current;
}

function resolvePath(path) {
    if (path.startsWith('~/')) {
        return path;
    } else if (path.startsWith('/')) {
        // Chemin absolu - pour simplifier, on ne gère que ~
        return '~';
    } else if (path === '..') {
        return '~';
    } else {
        // Chemin relatif
        if (currentDirectory === '~') {
            return `~/${path}`;
        } else {
            return `${currentDirectory}/${path}`;
        }
    }
}

function listDirectory(contents) {
    if (!contents || Object.keys(contents).length === 0) {
        return { output: '', type: 'output' };
    }
    
    const items = Object.keys(contents).map(name => {
        const item = contents[name];
        return item.type === 'directory' ? `${name}/` : name;
    });
    
    return {
        output: items.join('  '),
        type: 'output'
    };
}

function clearTerminal() {
    const terminalBody = document.getElementById('terminalBody');
    const inputLine = terminalBody.lastElementChild;
    terminalBody.innerHTML = '';
    terminalBody.appendChild(inputLine);
    
    // Ajouter un message de bienvenue
    addOutputLine('Terminal effacé. Tapez "help" pour voir les commandes disponibles.', 'success');
}

function resetTerminal() {
    currentDirectory = '~';
    commandHistory = [];
    historyIndex = -1;
    
    const terminalBody = document.getElementById('terminalBody');
    terminalBody.innerHTML = `
        <div class="terminal-line">
            <span class="terminal-prompt">utilisateur@linux:~$</span> Terminal réinitialisé !
        </div>
        <div class="terminal-line">
            <span class="terminal-prompt">utilisateur@linux:~$</span> Tapez <code>help</code> pour voir les commandes disponibles.
        </div>
        <div class="terminal-line">
            <span class="terminal-prompt">utilisateur@linux:~$</span> 
            <input type="text" class="terminal-input" id="terminalInput" autocomplete="off" spellcheck="false" placeholder="Tapez une commande ici...">
        </div>
    `;
    
    // Réattacher l'événement
    const terminalInput = document.getElementById('terminalInput');
    terminalInput.addEventListener('keydown', handleKeyPress);
    terminalInput.focus();
}

function scrollToBottom() {
    const terminalBody = document.getElementById('terminalBody');
    terminalBody.scrollTop = terminalBody.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
