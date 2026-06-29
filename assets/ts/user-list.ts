import { Config } from "datatables.net-dt";
import { ListPage } from "./classes/ListPage.ts";
import { ListPageOptions } from "./types/ListPageOptions.ts";

class UserList 
{
    constructor() {
        const datatableOptions: Config = {
            columnDefs: [
                { orderable: false, targets: [2,3] }
            ],
            order: [[1, 'desc']],
            columns: [
                {"data": "id"},
                {"data": "username"},
                {"data": "groups"},
                {"data": "actions"},
            ]
        };

        const pageOptions = {
            dataLoadRouteName: 'admin_users_load',
            searchFilterFormName: 'user_search'
        }

        new ListPage(datatableOptions, pageOptions);
    }
}

new UserList();