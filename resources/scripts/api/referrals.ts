import http from '@/api/http';

export interface ReferralInfo {
    enabled: boolean;
    code: string | null;
    referrer_bonus: number;
    referred_bonus: number;
    already_claimed: boolean;
    total_invited: number;
    invited: { username: string; rewarded_at: string }[];
}

export const getReferrals = (): Promise<ReferralInfo> =>
    http.get('/api/client/referrals').then(({ data }) => data);

export const claimReferral = (code: string): Promise<{ balance: number; bonus: number }> =>
    http.post('/api/client/referrals/claim', { code }).then(({ data }) => data);
