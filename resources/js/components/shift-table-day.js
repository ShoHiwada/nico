export default function (currentYear = '', currentMonthStr = '', daysArray = []){
    return {
        // --- 基本情報
        currentYear,
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
            const hasFilter =
                this.branch_id || this.department_id || this.position_id || this.shift_role;
        
            if (!hasFilter) {
                // 全件取得（初期状態に戻す）
                await this.fetchUsers();
                return;
            }
        
            const params = new URLSearchParams({
                branch_id: this.branch_id,
                department_id: this.department_id,
                position_id: this.position_id,
                shift_role: this.shift_role
            });
        
            const res = await fetch(`/admin/api/users?${params}`);
            this.users = await res.json();
        },        

        // 希望シフト反映
        async reflectShiftRequests() {
            if (this.selectedUserIds.length === 0) {
                alert("対象者が選択されていません。");
                return;
            }
        
            const params = new URLSearchParams();
            params.append('month', this.currentMonth);
            this.selectedUserIds.forEach(id => params.append('user_ids[]', id));
        
            try {
                const res = await fetch(`/admin/api/shift-requests?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
        
                const data = await res.json();
                console.log("📥 希望シフトデータ:", data);
        
                data.forEach(({ user_id, date, week_patterns }) => {
                    if (!this.shiftData[date]) this.shiftData[date] = {};
                    
                    // 🛡️ 防御的に型チェック
                    const patterns = Array.isArray(week_patterns)
                        ? week_patterns
                        : typeof week_patterns === 'string'
                        ? JSON.parse(week_patterns || '[]')
                        : [];
                
                    console.log(`▶ user: ${user_id}, date: ${date}, patterns:`, patterns); // ← ここに移動
                
                    this.shiftData[date][user_id] = patterns.map(Number);
                });
                
                
        
                alert(`希望シフトを反映しました（${this.selectedUserIds.length}名）`);
                console.log("📝 反映後の shiftData:", this.shiftData);
        
            } catch (error) {
                console.error("❌ シフト希望の取得に失敗しました", error);
                alert("シフト希望の取得に失敗しました。");
            }
        },            

        formatDate(day) {
            const year = window.currentYear;
            const month = String(this.currentMonth).padStart(2, '0');
            const date = String(day).padStart(2, '0');
            return `${year}-${month}-${date}`; // "2025-05-14"
        },        

        // 固定シフト反映
        async reflectFixedShifts() {
            if (this.selectedUserIds.length === 0) {
                alert("対象者が選択されていません。");
                return;
            }
        
            const params = new URLSearchParams();
            this.selectedUserIds.forEach(id => params.append('user_ids[]', id));
        
            try {
                const res = await fetch(`/admin/api/fixed-shifts?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
        
                const data = await res.json();
                console.log("📦 固定シフトデータ:", data);
        
                data.forEach(({ user_id, week_patterns }) => {
                    const parsed = typeof week_patterns === 'string'
                        ? JSON.parse(week_patterns)
                        : week_patterns;
        
                    for (const week in parsed) {
                        for (const dow in parsed[week]) {
                            const typeIds = parsed[week][dow].map(Number);
                            const date = this.resolveDateFromWeekAndDow(Number(week), Number(dow));
        
                            if (!this.shiftData[date]) this.shiftData[date] = {};
                            this.shiftData[date][user_id] = [...typeIds];
                        }
                    }
                });
        
                alert(`固定シフトを反映しました（${this.selectedUserIds.length}名）`);
                console.log("✅ shiftData after fixed:", this.shiftData);
        
            } catch (e) {
                console.error("❌ 固定シフト取得エラー:", e);
                alert("固定シフトの取得に失敗しました。");
            }
        },
        
        resolveDateFromWeekAndDow(week, dow) {
            const base = new Date(`${this.currentMonth}-01`);
            const startDow = base.getDay();
            const offset = (week - 1) * 7 + ((dow + 7 - startDow) % 7);
            base.setDate(base.getDate() + offset);
            return base.toISOString().slice(0, 10);
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

        // ダミー：必要なら後で実装
        getRequestsForUser(userId) {
            return {};
        }
    }
}
