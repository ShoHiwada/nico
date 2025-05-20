import { scoreOptionDefaults } from '../constants/scoreOptionDefaults';
import { scoreOptionLabels } from '../constants/scoreOptionLabels';
import { calculateScore } from '../utils/shiftScore';
import _ from 'lodash';

export default function () {
    return {
        assignments: window.assignments || {},
        users: window.users || [],
        userColors: window.userColors || {},
        shiftRequests: window.shiftRequests || {},
        shiftTypeCategories: window.shiftTypeCategories || {},
        dates: window.dates || [],
        buildings: window.buildings || [],
        selectedUserIds: [],
        targetDate: '',
        targetBuilding: '',
        showModal: false,
        filteredUsers: [],
        showPrioritySettings: false,
        userFlags: {},
        selectedAssignments: [],

        scoreOptions: _.cloneDeep(scoreOptionDefaults),
        labelMap: scoreOptionLabels,

        debugScoreLog: false, // デバッグフラグ

        formatDate(date) {
            const d = new Date(date);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        },

        editCell(date, buildingId) {
            this.targetDate = this.formatDate(date);
            this.targetBuilding = buildingId;

            const existingUsers = this.assignments[date]?.[buildingId] ?? [];
            this.selectedUserIds = existingUsers.map(u => u.id.toString());

            this.filteredUsers = this.users.filter(user =>
                user.shift_role === 'night' || user.shift_role === 'both'
            );

            this.showModal = true;
        },

        isNightShiftPreferred(userId, date) {
            const ids = this.shiftRequests?.[date]?.[userId] ?? [];
            return ids.some(id => this.shiftTypeCategories[parseInt(id)] === 'night');
        },

        applySelection() {
            const selections = {};
        
            for (const entry of this.selectedAssignments) {
                const [typeId, userId] = entry.split("-").map(Number);
                const user = this.users.find(u => u.id === userId);
                if (!selections[typeId]) selections[typeId] = [];
        
                selections[typeId].push({
                    id: user.id,
                    name: user.name,
                    shift_role: user.shift_role,
                    color: this.userColors[user.id] || 'bg-gray-200',
                    shift_type_id: typeId
                });
            }
        
            if (!this.assignments[this.targetDate]) this.assignments[this.targetDate] = {};
            this.assignments[this.targetDate][this.targetBuilding] = selections;
            this.showModal = false;
        },

        submit() {
            fetch(window.storeShiftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ assignments: this.assignments })
            })            
                .then(res => res.ok ? res.json() : Promise.reject('保存失敗'))
                .then(data => alert(data.message))
                .catch(err => {
                    console.error(err);
                    alert('エラーが発生しました');
                });
        },

        assignAutomatically() {
            const isPreferred = (userId, date) => this.isNightShiftPreferred(userId, date);
            const isAlreadyAssigned = (userId, date) =>
                Object.values(this.assignments?.[date] || {}).some(users =>
                    users.some(u => u.id === userId)
                );

            for (const { date } of this.dates) {
                for (const building of this.buildings) {
                    const bid = building.id;
                    if (this.assignments[date]?.[bid]) continue;

                    const candidates = this.users
                        .filter(user => {
                            return (
                                (user.shift_role === 'night' || user.shift_role === 'both') &&
                                isPreferred(user.id, date) &&
                                !isAlreadyAssigned(user.id, date)
                            );
                        })
                        .map(user => {
                            const score = calculateScore({
                                userId: user.id,
                                date,
                                users: this.users,
                                shiftRequests: this.shiftRequests,
                                assignments: this.assignments,
                                shiftTypeCategories: this.shiftTypeCategories,
                                userFlags: this.userFlags,
                                scoreOptions: this.scoreOptions
                            });
                            return { ...user, score };
                        })
                        .sort((a, b) => b.score - a.score);

                    // デバッグログ出したい時だけ表示
                    if (this.debugScoreLog) {
                        this.logCandidateScores(date, building.name || `ID:${building.id}`, candidates);
                    }

                    const selected = candidates.slice(0, 1).map(user => ({
                        id: user.id,
                        name: user.name,
                        shift_role: user.shift_role,
                        color: this.userColors[user.id] || 'bg-gray-200'
                    }));

                    if (!this.assignments[date]) this.assignments[date] = {};
                    this.assignments[date][bid] = selected;
                }
            }

            alert("希望者ベースで自動割当を実行しました！");
        },

        logCandidateScores(date, buildingName, candidates) {
            console.log(`📅 日付: ${date}`);
            console.log(`🏢 建物: ${buildingName}`);
            if (candidates.length === 0) {
                console.log("⚠ 希望者なし");
                return;
            }
            candidates.forEach((u, i) => {
                console.log(`  ${i + 1}. ${u.name}（ID:${u.id}）→ スコア: ${u.score}`);
            });
        },

        withScoreLog(callback) {
            console.log("=== 🧠 自動割当スコアログ START ===");
            callback();
            console.log("=== 🧠 自動割当スコアログ END ===");
        },
    };
};
