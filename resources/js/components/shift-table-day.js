export default function (currentMonthStr = '', daysArray = []) {
    return {
        // --- 基本情報
        currentMonth: currentMonthStr,
        days: daysArray,

        // --- フィルター条件
        branch_id: '',
        department_id: '',
        position_id: '',
        shift_role: '',
        branches: [],
        departments: [],
        positions: [],

        // --- データ管理
        users: [],
        shiftTypes: [],
        shiftData: {},
        deletedDates: [],

        // --- モーダル
        modalOpen: false,
        selectedUserId: null,
        selectedUserName: '',
        selectedDate: '',
        selectedTypes: [],

        // --- 複数選択用
        selectedUserIds: [],

        async init() {
            await this.fetchUsers();
            await this.fetchShiftTypes();
            await this.fetchShiftData();
            await this.fetchFilters();
        },

        async fetchUsers() {
            const res = await fetch('/admin/api/users');
            this.users = await res.json();
        },

        async fetchShiftTypes() {
            const res = await fetch('/admin/api/shift-types');
            this.shiftTypes = await res.json();
        },

        async fetchShiftData() {
            const res = await fetch(`/admin/shifts/fetch?month=${this.currentMonth}`);
            const data = await res.json();

            data.forEach(({ date, user_id, shift_type_id }) => {
                if (!this.shiftData[date]) this.shiftData[date] = {};
                if (!this.shiftData[date][user_id]) this.shiftData[date][user_id] = [];
                if (!this.shiftData[date][user_id].includes(shift_type_id)) {
                    this.shiftData[date][user_id].push(shift_type_id);
                }
            });
        },

        async fetchFilters() {
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

            const res = await fetch(`/admin/api/users?${params}`);
            this.users = await res.json();
        },

        formatDate(day) {
            return `${this.currentMonth}-${String(day).padStart(2, '0')}`;
        },

        openModal(userId, userName, date) {
            this.selectedUserId = userId;
            this.selectedUserName = userName;
            this.selectedDate = date;
            this.selectedTypes = this.shiftData[date]?.[userId] ? [...this.shiftData[date][userId]] : [];
            this.modalOpen = true;
        },

        saveSelection() {
            const date = this.selectedDate;
            const userId = this.selectedUserId;

            if (!this.shiftData[date]) this.shiftData[date] = {};

            if (this.selectedTypes.length === 0) {
                delete this.shiftData[date][userId];
                if (!this.deletedDates.find(d => d.date === date && d.user_id === userId)) {
                    this.deletedDates.push({ date, user_id: userId });
                }
            } else {
                this.shiftData[date][userId] = [...this.selectedTypes];
                this.deletedDates = this.deletedDates.filter(d => !(d.date === date && d.user_id === userId));
            }

            this.modalOpen = false;
        },

        getLabel(date, userId) {
            const types = this.shiftData[date]?.[userId] || [];
            return types.map(id => this.shiftTypes.find(t => t.id == id)?.name || '').join('<br>');
        },

        hasShift(date, userId) {
            return this.shiftData?.[date]?.[userId]?.length > 0;
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
        },

        toggleAllUsers(checked) {
            this.selectedUserIds = checked ? this.users.map(u => u.id) : [];
        },

        reflectShiftRequests() {
            if (this.selectedUserIds.length === 0) {
                alert("対象者が選択されていません。");
                return;
            }

            this.selectedUserIds.forEach(userId => {
                const requests = this.getRequestsForUser(userId);
                for (const [date, types] of Object.entries(requests)) {
                    if (!this.shiftData[date]) this.shiftData[date] = {};
                    this.shiftData[date][userId] = types;
                }
            });

            alert("希望シフトを反映しました。");
        },

        // ダミー：必要なら後で実装
        getRequestsForUser(userId) {
            return {};
        }
    }
}
