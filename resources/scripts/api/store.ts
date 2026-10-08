import http from '@/api/http';

export interface ServerPlan {
    id: number;
    name: string;
    description: string | null;
    memory: number;
    cpu: number;
    disk: number;
    price_coins: number;
    duration_days: number;
    active: boolean;
}

export interface CoinEntry {
    id: number;
    amount: number;
    reason: string;
    meta: Record<string, unknown> | null;
    created_at: string;
}

export interface PurchaseResult {
    balance: number;
    server_id: string;
    message: string;
}

export const getStore = (): Promise<{ balance: number; plans: ServerPlan[] }> =>
    http.get('/api/client/store').then(({ data }) => data);

export const getLedger = (): Promise<{ balance: number; entries: CoinEntry[] }> =>
    http.get('/api/client/store/ledger').then(({ data }) => data);

export const purchasePlan = (planId: number, name: string): Promise<PurchaseResult> =>
    http.post('/api/client/store/purchase', { plan_id: planId, name }).then(({ data }) => data);

// --- Rewarded ads ---

export interface AdStatus {
    enabled: boolean;
    can_watch: boolean;
    reward_coins: number;
    watched_today: number;
    daily_limit: number;
    cooldown_seconds: number;
    cooldown_ends_in: number;
    has_provider_ad: boolean;
    demo_duration_seconds: number;
}

export interface AdSession {
    token: string;
    expires_at: string;
    reward_coins: number;
    ad_html: string | null;
    demo_duration_seconds: number;
}

export interface AdCompleteResult {
    coins_earned: number;
    balance: number;
    entry_id: number;
}

export const getAdStatus = (): Promise<AdStatus> =>
    http.get('/api/client/ads/status').then(({ data }) => data);

export const startAd = (): Promise<AdSession> =>
    http.post('/api/client/ads/start').then(({ data }) => data);

export const completeAd = (token: string): Promise<AdCompleteResult> =>
    http.post('/api/client/ads/complete', { token }).then(({ data }) => data);

// --- Manual coin top-ups (Buy Coins) ---

export interface TopupPackage {
    id: number;
    name: string;
    coins: number;
    price: number;
    price_base: number;
}

export interface TopupMethod {
    key: string;
    label: string;
    account: string;
    instructions: string;
}

export interface TopupOptions {
    enabled: boolean;
    packages: TopupPackage[];
    methods: TopupMethod[];
    currency: string;
    currency_symbol: string;
    base_currency: string;
    base_currency_symbol: string;
}

export interface TopupRequest {
    id: number;
    package: string | null;
    coins: number;
    price: number;
    method: string;
    transaction_ref: string;
    status: 'pending' | 'approved' | 'rejected';
    admin_note: string | null;
    created_at: string;
}

export interface TopupSubmitResult {
    id: number;
    status: string;
    message: string;
}

export const getTopupOptions = (): Promise<TopupOptions> =>
    http.get('/api/client/topups').then(({ data }) => data);

export const submitTopup = (
    packageId: number,
    method: string,
    transactionRef: string,
    whatsapp: string,
    email: string
): Promise<TopupSubmitResult> =>
    http
        .post('/api/client/topups', { package_id: packageId, method, transaction_ref: transactionRef, whatsapp, email })
        .then(({ data }) => data);

export const getTopupHistory = (): Promise<{ data: TopupRequest[] }> =>
    http.get('/api/client/topups/history').then(({ data }) => data);
