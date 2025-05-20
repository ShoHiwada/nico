export default function shiftFilter(initialUsers = []) {
    return {
        branch_id: '',
        department_id: '',
        position_id: '',
        shift_role: '',
        branches: [],
        departments: [],
        positions: [],
        users: initialUsers, // 初期値

        async init() {
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

            const res = await fetch(`/admin/users?${params}`);
            this.users = await res.json();
        }
    }
}
