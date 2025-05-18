export default function (typesFromBackend = [], shiftsFromBackend = {}) {
    return {
        modalOpen: false,
        selectedUserId: null,
        selectedUserName: '',
        selectedDate: '',
        selectedTypes: [],
        shiftData: {},
        deletedDates: [],
        shiftTypes: typesFromBackend,
        initialShiftData: shiftsFromBackend, 

        init() {
            // 既存のシフト情報をセット
            for (const date in this.initialShiftData) {
                this.shiftData[date] = this.shiftData[date] || {};
                for (const userId in this.initialShiftData[date]) {
                    this.shiftData[date][userId] = [...this.initialShiftData[date][userId]];
                }
            }
        },

        openModal(userId, userName, date) {
            this.selectedUserId = userId;
            this.selectedUserName = userName;
            this.selectedDate = date;
            this.selectedTypes = this.shiftData[date]?.[userId] ? [...this.shiftData[date][userId]] : [];
            this.modalOpen = true;
        },

        saveSelection() {
            if (!this.shiftData[this.selectedDate]) {
                this.shiftData[this.selectedDate] = {};
            }

            if (this.selectedTypes.length === 0) {
                // 選択が空なら削除としてマーク
                delete this.shiftData[this.selectedDate][this.selectedUserId];

                // 削除対象として記録（既に追加されていないかチェック）
                const existing = this.deletedDates.find(
                    d => d.date === this.selectedDate && d.user_id === this.selectedUserId
                );
                if (!existing) {
                    this.deletedDates.push({
                        date: this.selectedDate,
                        user_id: this.selectedUserId
                    });
                }
            } else {
                // 選択があるなら通常保存
                this.shiftData[this.selectedDate][this.selectedUserId] = [...this.selectedTypes];

                // 削除対象から除外（再登録された場合）
                this.deletedDates = this.deletedDates.filter(
                    d => !(d.date === this.selectedDate && d.user_id === this.selectedUserId)
                );
            }

            this.modalOpen = false;
        },

        getLabel(date, userId) {
            const types = this.shiftData[date]?.[userId] || [];
            return types.map(id => {
                const found = this.shiftTypes.find(t => t.id == id);
                return found ? found.name : '';
            }).join('<br>');
        },

        hasShift(date, userId) {
            return (this.shiftData[date]?.[userId] || []).length > 0;
        },

        prevDate(date) {
            const d = new Date(date);
            d.setDate(d.getDate() - 1);
            return d.toISOString().slice(0, 10);
        },

        nextDate(date) {
            const d = new Date(date);
            d.setDate(d.getDate() + 1);
            return d.toISOString().slice(0, 10);
        },

        isConsecutiveShift(date, userId) {
            return this.hasShift(this.prevDate(date), userId) || this.hasShift(this.nextDate(date), userId);
        },

        // シフトをスライム
        getShiftClass(date, userId) {
            const has = this.hasShift(date, userId);
            const hasPrev = this.hasShift(this.prevDate(date), userId);
            const hasNext = this.hasShift(this.nextDate(date), userId);
        
            if (!has) return '';
            if (!hasPrev && !hasNext) return 'rounded-full';
            if (!hasPrev && hasNext) return 'rounded-s-full'; // 左だけ
            if (hasPrev && !hasNext) return 'rounded-e-full'; // 右だけ    
            return 'rounded-none';
        }        
    };
}
