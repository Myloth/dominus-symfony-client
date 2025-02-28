import "datatables.net-dt";
import DataList from "./js/components/DataList";

$(document).ready( function() {
    let list = DataList
    let pageOptions = {
        dataLoadRouteName: 'admin_users_group_load',
        searchFilterFormName: 'user_group_search',
    }

    let datatableOptions = {
        columnDefs: [
            { orderable: false, targets: [2,3] }
        ],
        order: [[1, 'desc']],
        orderSequence: ['asc', 'desc'], // specify the order in which columns can be ordered
        columns: [
            {"data": "id"},
            {"data": "name"},
            {"data": "roles"},
            {"data": "actions"},
        ]
    }

    list.init(pageOptions, datatableOptions);
});

