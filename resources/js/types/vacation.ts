// Shared types for vacation management components
import { UserRole } from '@/enums/UserRole';
import { VacationRequestStatus } from '@/enums/VacationRequestStatus';
import { VacationRequestType } from '@/enums/VacationRequestType';

// Re-export enums for convenience
export { UserRole, VacationRequestStatus, VacationRequestType };

export interface Employee {
    id: string;
    name: string;
    email: string;
    department: string;
    totalDays: number;
    usedDays: number;
    pendingRequests: number;
}

export interface VacationRequest {
    id: string;
    startDate: Date;
    endDate: Date;
    type: VacationRequestType;
    status: VacationRequestStatus;
    reason?: string;
    employeeName?: string;
    department?: string;
    document?: File;
}

export const STATUS_COLORS: Record<VacationRequestStatus, string> = {
    [VacationRequestStatus.PENDING]:
        'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
    [VacationRequestStatus.APPROVED]:
        'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    [VacationRequestStatus.REJECTED]:
        'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
};
