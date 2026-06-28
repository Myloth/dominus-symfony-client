import { EditParams} from "./types/EditParams.ts";
import { EditPage} from "./classes/EditPage.ts";

class GroupEdit {
    constructor() {
        let dataDiv = document.getElementById('dataDiv')
        if (dataDiv) {
            let editIdValue: string | null = dataDiv.getAttribute('data-id');
            let editId: number | null = editIdValue ? Number(editIdValue) : null;

            let params: EditParams = {
                saveButton: "saveButton",
                editId: editId,
                formName: "group",
                saveRouteName: "admin_users_group_new"
            }

            new EditPage(params);
        }
    }
}

new GroupEdit();
