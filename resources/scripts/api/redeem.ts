import http from '@/api/http';

export interface RedeemClaimResult {
    balance: number;
    coins: number;
    message: string;
}

export const claimRedeemCode = (code: string): Promise<RedeemClaimResult> =>
    http.post('/api/client/redeem/claim', { code }).then(({ data }) => data);
