import { prisma } from './prisma.js';
const sessions = new Map();
export function setUserSession(telegramId, role, ticketId, orderState, orderData) {
    sessions.set(telegramId, { role, ticketId, orderState, orderData });
}
export function updateUserSession(telegramId, partialSession) {
    const current = sessions.get(telegramId) || { role: 'customer' };
    sessions.set(telegramId, { ...current, ...partialSession });
}
export function getUserSession(telegramId) {
    return sessions.get(telegramId);
}
export function clearUserSession(telegramId) {
    sessions.delete(telegramId);
}
export async function createTicket(customerId, customerName, initialMessage, websiteUserId) {
    let user = await prisma.user.findUnique({ where: { telegramId: customerId } });
    if (!user) {
        user = await prisma.user.create({
            data: {
                telegramId: customerId,
                name: customerName,
                email: `${customerId}@telegram.local`,
                password: 'dummy',
            }
        });
    }
    const count = await prisma.ticket.count();
    const ticketIdStr = `TCK-${String(count + 1).padStart(4, '0')}`;
    const ticket = await prisma.ticket.create({
        data: {
            ticketId: ticketIdStr,
            userId: user.id,
            category: 'general',
            messages: {
                create: {
                    senderType: 'customer',
                    message: initialMessage
                }
            }
        },
        include: {
            messages: true
        }
    });
    return ticket;
}
export async function getTicketById(ticketId) {
    const ticket = await prisma.ticket.findUnique({
        where: { ticketId },
        include: { messages: true }
    });
    return ticket;
}
export async function listOpenTickets() {
    const tickets = await prisma.ticket.findMany({
        where: { status: { not: 'closed' } },
        orderBy: { createdAt: 'desc' },
        include: { messages: true }
    });
    return tickets;
}
export async function listAdminTickets() {
    const tickets = await prisma.ticket.findMany({
        orderBy: { updatedAt: 'desc' },
        include: { messages: true }
    });
    return tickets;
}
export async function getCustomerTicket(customerId) {
    const user = await prisma.user.findUnique({ where: { telegramId: customerId } });
    if (!user)
        return null;
    const ticket = await prisma.ticket.findFirst({
        where: {
            userId: user.id,
            status: { not: 'closed' }
        },
        orderBy: { updatedAt: 'desc' },
        include: { messages: true }
    });
    return ticket;
}
export async function getOrCreateCustomerTicket(customerId, customerName, initialMessage, websiteUserId) {
    const existing = await getCustomerTicket(customerId);
    if (existing) {
        return existing;
    }
    return createTicket(customerId, customerName, initialMessage, websiteUserId);
}
export async function appendMessage(ticketId, sender, text) {
    const ticket = await prisma.ticket.findUnique({ where: { ticketId } });
    if (!ticket)
        return null;
    await prisma.ticketMessage.create({
        data: {
            ticketId: ticket.id,
            senderType: sender,
            message: text
        }
    });
    const updatedTicket = await prisma.ticket.update({
        where: { ticketId },
        data: { lastMessageAt: new Date(), updatedAt: new Date(), messagesCount: { increment: 1 } },
        include: { messages: true }
    });
    return updatedTicket;
}
export async function assignTicket(ticketId, csId, csName) {
    let csAgent = await prisma.csAgent.findUnique({ where: { telegramId: csId } });
    if (!csAgent) {
        csAgent = await prisma.csAgent.create({
            data: {
                telegramId: csId,
                name: csName || `CS Agent ${csId}`,
                email: `${csId}@cs.telegram.local`,
                status: 'online',
            }
        });
    }
    const ticket = await prisma.ticket.update({
        where: { ticketId },
        data: {
            csAgentId: csAgent.id,
            status: 'assigned',
            assignedAt: new Date()
        },
        include: { messages: true }
    });
    return ticket;
}
export async function updateTicketStatus(ticketId, status) {
    const data = { status };
    if (status === 'closed') {
        data.closedAt = new Date();
    }
    const ticket = await prisma.ticket.update({
        where: { ticketId },
        data,
        include: { messages: true }
    });
    return ticket;
}
export function ticketSummary(ticket) {
    const lastMessage = ticket.messages?.length > 0 ? ticket.messages[ticket.messages.length - 1].message : '-';
    return [
        `#${ticket.ticketId}`,
        `Status: ${ticket.status}`,
        `Prioritas: ${ticket.priority}`,
        `Pesan terakhir: ${lastMessage}`,
    ].join('\n');
}
export function ticketListText(ticketsToShow) {
    if (ticketsToShow.length === 0) {
        return 'Belum ada tiket yang tersedia.';
    }
    return ticketsToShow
        .slice(0, 5)
        .map((ticket) => {
        const lastMsg = ticket.messages?.length > 0 ? ticket.messages[ticket.messages.length - 1].message : '-';
        return `• ${ticket.ticketId} | ${ticket.status} | ${lastMsg}`;
    })
        .join('\n');
}
export async function listCsTickets(telegramId) {
    const csAgent = await prisma.csAgent.findUnique({ where: { telegramId } });
    if (!csAgent)
        return [];
    const tickets = await prisma.ticket.findMany({
        where: {
            csAgentId: csAgent.id,
            status: { not: 'closed' }
        },
        orderBy: { createdAt: 'desc' },
        include: { messages: true }
    });
    return tickets;
}
export async function rateTicket(ticketId, rating) {
    const ticket = await prisma.ticket.update({
        where: { ticketId },
        data: { rating }
    });
    if (ticket.csAgentId) {
        // Calculate new average rating for the CS agent
        const result = await prisma.ticket.aggregate({
            where: {
                csAgentId: ticket.csAgentId,
                rating: { not: null }
            },
            _avg: { rating: true }
        });
        const avgRating = result._avg.rating || 0;
        await prisma.csAgent.update({
            where: { id: ticket.csAgentId },
            data: { rating: avgRating }
        });
    }
}
