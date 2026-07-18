import { buildAdminDashboardLink, buildCsListLink, buildCustomerSupportLink } from './telegramLink.js';
export function createWebsiteTriggerHelpers(botUsername) {
    return {
        customerLink(userId, ticketId) {
            return buildCustomerSupportLink(userId, ticketId, botUsername);
        },
        csLink() {
            return buildCsListLink(botUsername);
        },
        adminLink() {
            return buildAdminDashboardLink(botUsername);
        },
    };
}
