import { scoreOptionDefaults } from '../constants/scoreOptionDefaults';
import { scoreOptionLabels } from '../constants/scoreOptionLabels';
import { calculateScore } from '../utils/shiftScore';
import _ from 'lodash';

export default function () {
    return {
        // 初期データ
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

        // スコア系
        scoreOptions: _.cloneDeep(scoreOptionDefaults),
        labelMap: scoreOptionLabels,

        // シフト種別ごとの色
        shiftTypeColors: {
            4: 'bg-blue-500',
            5: 'bg-pink-500',
        },

        debugScoreLog: false,

        formatDate(date) {
            const d = new Date(date);
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        },

        editCell(date, buildingId) {
            this.targetDate = this.formatDate(date);
            this.targetBuilding = buildingId;

            const existing = this.assignments[this.targetDate]?.[this.targetBuilding] || {};
            this.selectedUserIds = Object.values(existing).flat().map(u => u.id.toString());

            this.filteredUsers = this.users.filter(user => user.shift_role === 'night' || user.shift_role === 'both');
            this.selectedAssignments = []; // 初期化
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
                const stringTypeId = String(typeId);
                const user = this.users.find(u => u.id === userId);
                if (!user) continue;

                if (!selections[stringTypeId]) selections[stringTypeId] = [];
                selections[stringTypeId].push({
                    id: user.id,
                    name: user.name,
                    shift_role: user.shift_role,
                    shift_type_id: typeId,
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
                body: JSON.stringify({ assignments: this.assignments }),
            })
                .then(res => res.ok ? res.json() : Promise.reject('保存失敗'))
                .then(data => alert(data.message))
                .catch(err => {
                    console.error(err);
                    alert('エラーが発生しました');
                });
        },

        assignAutomatically() {
            const nightTypeIds = Object.values(this.shiftTypeCategories || {})
                .filter(type => type.category === 'night')
                .map(type => type.id);


            const isPreferred = (userId, date) =>
                nightTypeIds.some(typeId =>
                    this.shiftRequests?.[date]?.[userId]?.includes(String(typeId))
                );

            const isAlreadyAssigned = (userId, date) =>
                Object.values(this.assignments?.[date] || {}).some(shiftGroups =>
                    Object.values(shiftGroups).flat().some(u => u.id === userId)
                );

            for (const { date } of this.dates) {
                for (const building of this.buildings) {
                    const bid = building.id;
                    if (this.assignments[date]?.[bid]) continue;

                    const candidates = this.users
                        .filter(user =>
                            (user.shift_role === 'night' || user.shift_role === 'both') &&
                            isPreferred(user.id, date) &&
                            !isAlreadyAssigned(user.id, date)
                        )

                        .map(user => {
                            let shiftRequestArray = this.shiftRequests?.[date]?.[user.id];

                            if (typeof shiftRequestArray === 'string') {
                                try {
                                    shiftRequestArray = JSON.parse(shiftRequestArray);
                                } catch (e) {
                                    console.warn("❌ shiftRequestArrayのJSONパース失敗:", shiftRequestArray);
                                    shiftRequestArray = [];
                                }
                            }

                            shiftRequestArray = shiftRequestArray || [];

                            const preferredTypeId = nightTypeIds.find(typeId =>
                                shiftRequestArray.includes(String(typeId))
                            );
                            const finalTypeId = preferredTypeId ?? nightTypeIds[0];

                            return {
                                ...user,
                                preferredTypeId: finalTypeId,
                                score: calculateScore({
                                    userId: user.id,
                                    date,
                                    users: Object.values(this.users),
                                    typeId: finalTypeId,
                                    shiftRequests: this.shiftRequests,
                                    assignments: this.assignments,
                                    shiftTypeCategories: this.shiftTypeCategories,
                                    userFlags: this.userFlags,
                                    scoreOptions: this.scoreOptions
                                })
                            };
                        })

                        .sort((a, b) => b.score - a.score);

                    if (this.debugScoreLog) {
                        this.logCandidateScores(date, building.name || `ID:${building.id}`, candidates);
                    }

                    const selected = candidates.slice(0, 1).map(user => ({
                        id: user.id,
                        name: user.name,
                        shift_role: user.shift_role,
                        shift_type_id: user.preferredTypeId
                    }));

                    const selections = {};
                    if (selected.length) {
                        const typeId = selected[0].shift_type_id;
                        if (typeId) {
                            selections[String(typeId)] = selected;
                        } else {
                            console.warn("❗ shift_type_idが未定義:", selected);
                        }
                    }


                    if (!this.assignments[date]) this.assignments[date] = {};
                    this.assignments[date][bid] = selections;
                }
            }

            alert("希望者ベースで自動割当を実行しました！");
        },

        logCandidateScores(date, buildingName, candidates) {
            console.log(`📅 日付: ${date}`);
            console.log(`🏢 建物: ${buildingName}`);
            if (candidates.length === 0) return console.log("⚠ 希望者なし");
            candidates.forEach((u, i) => console.log(`  ${i + 1}. ${u.name}（ID:${u.id}）→ スコア: ${u.score}`));
        },

        withScoreLog(callback) {
            console.log("=== 🧠 自動割当スコアログ START ===");
            callback();
            console.log("=== 🧠 自動割当スコアログ END ===");
        }
    };
}
