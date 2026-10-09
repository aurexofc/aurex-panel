import http from '@/api/http';

export interface PremiumPackage {
    id: number;
    name: string;
    slug: string;
    duration_days: number;
    duration_label: string;
    is_lifetime: boolean;
    price_coins: number;
    max_servers: number;
    ads_free: boolean;
}

export interface PremiumStatus {
    balance: number;
    is_premium: boolean;
    max_servers: number;
    subscription: {
        package_name: string;
        expires_at: string | null;
        is_lifetime: boolean;
    } | null;
    packages: PremiumPackage[];
}

export interface PremiumPurchaseResult {
    balance: number;
    is_premium: boolean;
    expires_at: string | null;
    is_lifetime: boolean;
    message: string;
}

export const getPremiumStatus = (): Promise<PremiumStatus> =>
    http.get('/api/client/premium').then(({ data }) => data);

export const purchasePremium = (packageId: number): Promise<PremiumPurchaseResult> =>
    http.post('/api/client/premium/purchase', { package_id: packageId }).then(({ data }) => data);
