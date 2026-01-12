export enum VacationRequestType {
    VACATION = 'vacation',
    SICK = 'sick',
    PERSONAL = 'personal',
    UNPAID = 'unpaid',
    WFH = 'wfh',
}

export const TYPE_LABELS: Record<VacationRequestType, string> = {
    [VacationRequestType.VACATION]: 'Paid Leave',
    [VacationRequestType.SICK]: 'Sick Leave',
    [VacationRequestType.PERSONAL]: 'Personal Day',
    [VacationRequestType.UNPAID]: 'Unpaid Leave',
    [VacationRequestType.WFH]: 'Work From Home',
};

export const TYPE_COLORS: Record<VacationRequestType, string> = {
    [VacationRequestType.VACATION]: 'bg-blue-500',
    [VacationRequestType.SICK]: 'bg-red-500',
    [VacationRequestType.PERSONAL]: 'bg-green-500',
    [VacationRequestType.UNPAID]: 'bg-gray-500',
    [VacationRequestType.WFH]: 'bg-purple-500',
};
