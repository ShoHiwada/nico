export default function (typesFromBackend = []) {
    return {
        modalOpen: false,
        selectedUserId: null,
        selectedUserName: '',
        selectedDate: '',
        selectedTypes: [],
        shiftData: {},
        shiftTypes: typesFromBackend,

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
            this.shiftData[this.selectedDate][this.selectedUserId] = [...this.selectedTypes];
            this.modalOpen = false;
        },
        getLabel(date, userId) {
            const types = this.shiftData[date]?.[userId] || [];
            return types.map(id => {
                const found = this.shiftTypes.find(t => t.id == id);
                return found ? found.name : '';
            }).join('/');
        }
        
    };
}
