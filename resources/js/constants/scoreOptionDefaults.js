export const scoreOptionDefaults = {
    nightPreferred: { enabled: true, value: 10 },
    fewRequests: { enabled: true, value: 5 },
    bothRole: { enabled: true, value: 3 },
    consecutive: { enabled: true, value: -10 },
    tooManyAssignments: { enabled: true, value: -5 },
    workedYesterday: { enabled: false, value: -3 },
    hasNoAssignmentYet: { enabled: false, value: 4 },
    isHighPriorityUser: { enabled: false, value: 10 },
    isLowPriorityUser: { enabled: false, value: -10 },
};
