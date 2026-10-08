import http from '@/api/http';

export interface Prebot {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    github_url: string;
    icon: string;
    price_coins: number;
    memory: number;
    disk: number;
    cpu: number;
    featured: boolean;
    active: boolean;
    sort_order: number;
}

export interface PrebotPurchaseResult {
    balance: number;
    server_id: string;
    message: string;
}

export const getPrebots = (): Promise<{ balance: number; prebots: Prebot[] }> =>
    http.get('/api/client/prebots').then(({ data }) => data);

export const purchasePrebot = (prebotId: number, name: string): Promise<PrebotPurchaseResult> =>
    http.post('/api/client/prebots/purchase', { prebot_id: prebotId, name }).then(({ data }) => data);
