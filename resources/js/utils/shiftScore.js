export function applyScoreOption(optionKey, condition, scoreOptions) {
    const option = scoreOptions[optionKey];
    return option?.enabled && condition ? option.value : 0;
}


export function calculateScore({
    userId,
    date,
    users,
    shiftRequests,
    assignments,
    shiftTypeCategories,
    userFlags,
    scoreOptions
}) {
    const shiftRole = users.find(u => u.id === userId)?.shift_role || 'day';
    const totalRequestCount = Object.values(shiftRequests || {}).reduce((sum, day) => {
        return sum + (day[userId]?.length || 0);
    }, 0);

    const getRelativeDate = (d, offset) => {
        const dateObj = new Date(d);
        dateObj.setDate(dateObj.getDate() + offset);
        return dateObj.toISOString().slice(0, 10);
    };

    const isPrevAssigned = Object.values(assignments[getRelativeDate(date, -1)] || {})
    .flatMap(group => Object.values(group))
    .some(u => u.id === userId);

const isNextAssigned = Object.values(assignments[getRelativeDate(date, 1)] || {})
    .flatMap(group => Object.values(group))
    .some(u => u.id === userId);

const isTwoDaysAgoAssigned = Object.values(assignments[getRelativeDate(date, -2)] || {})
    .flatMap(group => Object.values(group))
    .some(u => u.id === userId);


    const assignedCount = Object.values(assignments || {}).reduce((sum, day) => {
        return sum + Object.values(day).reduce((innerSum, shiftGroups) =>
            innerSum + Object.values(shiftGroups).flat().filter(u => u.id === userId).length
        , 0);
    }, 0);
    

    const candidateCountToday = users.filter(user =>
        (user.shift_role === 'night' || user.shift_role === 'both') &&
        shiftRequests?.[date]?.[user.id]
    ).length;

    const isNightPreferred = shiftRequests?.[date]?.[userId]?.some(id => {
        const type = shiftTypeCategories?.[parseInt(id)];
        return type?.category === 'night';
    }) ?? false;
    
    const priority = userFlags?.[userId];

    let score = 0;

    score += applyScoreOption('nightPreferred', isNightPreferred, scoreOptions);
    score += applyScoreOption('fewRequests', totalRequestCount <= 3, scoreOptions);
    score += applyScoreOption('bothRole', shiftRole === 'both', scoreOptions);
    score += applyScoreOption('consecutive', isPrevAssigned || isNextAssigned, scoreOptions);
    score += applyScoreOption('tooManyAssignments', assignedCount >= 4, scoreOptions);
    score += applyScoreOption('workedYesterday', isPrevAssigned, scoreOptions);
    score += applyScoreOption('workedTwoDaysAgo', isTwoDaysAgoAssigned, scoreOptions);
    score += applyScoreOption('hasNoAssignmentYet', assignedCount === 0, scoreOptions);
    score += applyScoreOption('fewCandidatesToday', candidateCountToday <= 2, scoreOptions);
    score += applyScoreOption('isHighPriorityUser', priority === 'high', scoreOptions);
    score += applyScoreOption('isLowPriorityUser', priority === 'low', scoreOptions);

    return score;
}
