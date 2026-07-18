import fs from 'node:fs';
import path from 'node:path';
const storagePath = path.resolve(process.cwd(), 'data', 'websiteUsers.json');
function ensureStorageFile() {
    fs.mkdirSync(path.dirname(storagePath), { recursive: true });
    if (!fs.existsSync(storagePath)) {
        fs.writeFileSync(storagePath, JSON.stringify(defaultUsers(), null, 2), 'utf8');
    }
}
function defaultUsers() {
    return [
        {
            id: 12345,
            name: 'Rina Customer',
            email: 'rina@contoh.com',
            role: 'customer',
            activeTicketId: 'TCK-0001',
        },
        {
            id: 9001,
            name: 'CS One',
            email: 'cs1@contoh.com',
            role: 'cs',
            activeTicketId: 'TCK-0002',
        },
        {
            id: 9002,
            name: 'Admin One',
            email: 'admin@contoh.com',
            role: 'admin',
            activeTicketId: 'TCK-0003',
        },
    ];
}
function loadUsers() {
    ensureStorageFile();
    try {
        const content = fs.readFileSync(storagePath, 'utf8');
        const parsed = JSON.parse(content);
        return Array.isArray(parsed) ? parsed : defaultUsers();
    }
    catch {
        return defaultUsers();
    }
}
function saveUsers(users) {
    ensureStorageFile();
    fs.writeFileSync(storagePath, JSON.stringify(users, null, 2), 'utf8');
}
export function getWebsiteUserById(userId) {
    return loadUsers().find((user) => user.id === userId);
}
export function getWebsiteUserByEmail(email) {
    return loadUsers().find((user) => user.email.toLowerCase() === email.toLowerCase());
}
export function updateWebsiteUser(user) {
    const users = loadUsers();
    const index = users.findIndex((item) => item.id === user.id);
    if (index >= 0) {
        users[index] = user;
    }
    else {
        users.push(user);
    }
    saveUsers(users);
}
export function listWebsiteUsers() {
    return loadUsers();
}
