import { Config } from "datatables.net-dt";
import { ListPage } from "./classes/ListPage.ts";

class GroupList 
{
    constructor() {
        const datatableOptions: Config = {
            columnDefs: [
                { orderable: false, targets: [2,3] }
            ],
            order: [[1, 'desc']],
            columns: [
                {"data": "id"},
                {"data": "name"},
                {"data": "roles"},
                {"data": "actions"},
            ]
        };

        const pageOptions = {
            dataLoadRouteName: 'admin_users_group_load',
            searchFilterFormName: 'group_search'
        }

        new ListPage(datatableOptions, pageOptions);
    }
}

new GroupList();