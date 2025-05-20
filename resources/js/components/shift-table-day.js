export default function (typesFromBackend = [], shiftsFromBackend = {}, initialUsers = []) {
    return {
        // --- フィルター条件
        branch_id: '',
        department_id: '',
        position_id: '',
        shift_role: '',
        branches: [],
        departments: [],
        positions: [],
        users: initialUsers,

        // --- シフト管理
        shiftTypes: typesFromBackend,
        shiftData: {},
        initialShiftData: shiftsFromBackend,
        deletedDates: [],

        // --- モーダル管理
        modalOpen: false,
        selectedUserId: null,
        selectedUserName: '',
        selectedDate: '',
        selectedTypes: [],

        async init() {
            // 既存シフトを初期化
            for (const date in this.initialShiftData) {
                this.shiftData[date] = this.shiftData[date] || {};
                for (const userId in this.initialShiftData[date]) {
                    this.shiftData[date][userId] = [...this.initialShiftData[date][userId]];
                }
            }

            // フィルター用データ取得
            this.branches = await (await fetch('/admin/branches')).json();
            this.departments = await (await fetch('/admin/departments')).json();
            this.positions = await (await fetch('/admin/positions')).json();
        },

        get filteredDepartments() {
            if (!this.branch_id) return this.departments;
            return this.departments.filter(d => d.branch_id == this.branch_id);
        },

        async filterUsers() {
            const params = new URLSearchParams({
                branch_id: this.branch_id,
                department_id: this.department_id,
                position_id: this.position_id,
                shift_role: this.shift_role
            });

            const res = await fetch(`/admin/api/users?${params}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });            
            
            this.users = await res.json();
            console.log(typeof users, users);
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
                delete this.shiftData[this.selectedDate][this.selectedUserId];
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
                this.shiftData[this.selectedDate][this.selectedUserId] = [...this.selectedTypes];
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

        getShiftClass(date, userId) {
            const has = this.hasShift(date, userId);
            const hasPrev = this.hasShift(this.prevDate(date), userId);
            const hasNext = this.hasShift(this.nextDate(date), userId);
        
            if (!has) return '';
            if (!hasPrev && !hasNext) return 'rounded-full';
            if (!hasPrev && hasNext) return 'rounded-s-full';
            if (hasPrev && !hasNext) return 'rounded-e-full';
            return 'rounded-none';
        }
    };
}
