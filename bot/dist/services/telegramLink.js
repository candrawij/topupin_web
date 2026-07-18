import { config } from '../config.js';
import { encryptPayload } from './telegramPayload.js';
export function serializeLaunchPayload(payload) {
    return encryptPayload(payload);
}
export function buildTelegramDeepLink(options) {
    const botUsername = options.botUsername ?? config.botUsername;
    const payload = serializeLaunchPayload(options);
    return `https://t.me/${botUsername}?start=${encodeURIComponent(payload)}`;
}
export function buildCustomerSupportLink(userId, ticketId, botUsername) {
    return buildTelegramDeepLink({
        source: 'website',
        role: 'customer',
        userId,
        ticketId,
        action: 'open_ticket',
        botUsername,
    });
}
export function buildCsListLink(botUsername) {
    return buildTelegramDeepLink({
        source: 'website',
        role: 'cs',
        action: 'list',
        botUsername,
    });
}
export function buildAdminDashboardLink(botUsername) {
    return buildTelegramDeepLink({
        source: 'website',
        role: 'admin',
        action: 'dashboard',
        botUsername,
    });
}
