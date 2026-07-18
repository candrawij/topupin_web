import { prisma } from './prisma.js';
export async function getTransactionByTrxId(trxId) {
    const transaction = await prisma.transaction.findUnique({
        where: { trxId },
        include: {
            game: true,
            product: true,
        },
    });
    return transaction;
}
