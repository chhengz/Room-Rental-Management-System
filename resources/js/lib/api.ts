export type ApiResponse<T> = {
    success: boolean;
    message: string;
    data: T;
    errors?: Record<string, string[]>;
};

export type ApiError = Error & {
    status?: number;
    errors?: Record<string, string[]>;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
};

export type CurrentUser = {
    id: number;
    name: string;
    email: string;
    roles?: { name: string; label: string }[];
    permissions?: { name: string; label: string }[];
};

export type DashboardData = {
    rooms: {
        total: number;
        occupied: number;
        available: number;
        reserved: number;
        maintenance: number;
        occupancy_rate: number;
    };
    finance: {
        invoices_total: number;
        collected_total: number;
        outstanding_total: number;
        expenses_total: number;
        utilities_total: number;
        net_operating_result: number;
        overdue_invoice_count: number;
    };
    contracts: { upcoming_expiration_30_days: number };
};

const request = async <T>(
    path: string,
    options: RequestInit = {},
): Promise<ApiResponse<T>> => {
    const response = await fetch(`/api/v1${path}`, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...options.headers,
        },
        ...options,
    });
    const body = (await response.json()) as ApiResponse<T>;

    if (!response.ok) {
        const error = Object.assign(
            new Error(body.message || 'Request failed.'),
            { status: response.status, errors: body.errors },
        ) as ApiError;
        throw error;
    }

    return body;
};

const queryString = (params: Record<string, string | number | undefined>) => {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== '') query.set(key, String(value));
    });
    const value = query.toString();
    return value ? `?${value}` : '';
};

const resource = <T>(name: string) => ({
    list: (params: Record<string, string | number | undefined> = {}) =>
        request<Paginated<T>>(`/${name}${queryString(params)}`),
    get: (id: number) => request<T>(`/${name}/${id}`),
    create: (payload: Partial<T>) =>
        request<T>(`/${name}`, { method: 'POST', body: JSON.stringify(payload) }),
    update: (id: number, payload: Partial<T>) =>
        request<T>(`/${name}/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
    remove: (id: number) =>
        request<null>(`/${name}/${id}`, { method: 'DELETE' }),
});

export const api = {
    login: (email: string, password: string) =>
        request<CurrentUser>('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        }),
    me: () => request<CurrentUser>('/me'),
    logout: () => request<null>('/logout', { method: 'POST' }),
    dashboard: (fromDate?: string, toDate?: string) =>
        request<DashboardData>(
            `/dashboard${queryString({ from_date: fromDate, to_date: toDate })}`,
        ),
    settings: {
        list: () => request<Record<string, unknown>[]>('/settings'),
        update: (settings: Record<string, unknown>[]) =>
            request<Record<string, unknown>[]>('/settings', {
                method: 'PUT',
                body: JSON.stringify({ settings }),
            }),
    },
    buildings: resource<Record<string, unknown>>('buildings'),
    roomTypes: resource<Record<string, unknown>>('room-types'),
    rooms: resource<Record<string, unknown>>('rooms'),
    tenants: resource<Record<string, unknown>>('tenants'),
    contracts: resource<Record<string, unknown>>('contracts'),
    invoices: resource<Record<string, unknown>>('invoices'),
    payments: resource<Record<string, unknown>>('payments'),
    utilityMeters: resource<Record<string, unknown>>('utility-meters'),
    meterReadings: resource<Record<string, unknown>>('meter-readings'),
    expenses: resource<Record<string, unknown>>('expenses'),
};
