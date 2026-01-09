// Shared types for vacation management components

export interface Employee {
    id: string
    name: string
    email: string
    department: string
    totalDays: number
    usedDays: number
    pendingRequests: number
}

export interface VacationRequest {
    id: string
    startDate: Date
    endDate: Date
    type: VacationRequestType
    status: VacationRequestStatus
    reason?: string
    employeeName?: string
    department?: string
    document?: File
}

export type VacationRequestType = 'vacation' | 'sick' | 'personal' | 'unpaid' | 'wfh'

export type VacationRequestStatus = 'pending' | 'approved' | 'rejected'

export type UserRole = 'employee' | 'manager' | 'admin'

export const TYPE_LABELS: Record<VacationRequestType, string> = {
    vacation: 'Paid Leave',
    sick: 'Sick Leave',
    personal: 'Personal Day',
    unpaid: 'Unpaid Leave',
    wfh: 'Work From Home',
}

export const TYPE_COLORS: Record<VacationRequestType, string> = {
    vacation: 'bg-blue-500',
    sick: 'bg-red-500',
    personal: 'bg-green-500',
    unpaid: 'bg-gray-500',
    wfh: 'bg-purple-500',
}

export const STATUS_COLORS: Record<VacationRequestStatus, string> = {
    pending: 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20',
    approved: 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20',
    rejected: 'bg-red-500/10 text-red-700 dark:text-red-400 border-red-500/20',
}
