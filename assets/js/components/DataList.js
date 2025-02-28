import Datatable from 'datatables.net-dt';

/**
 * This object is made to aggregate all JS config for listing pages implementing a datatable. Each listing page must
 * implement this using the function init and providing mandatory arguments, or overriding existing to customize page rendering
 *
 * Mandatory parameters :
 * * pageOptions ( parameters used for the rendering of the page ):
 * ** searchFilterFormName - the name of the form containing the data filters
 * ** dataLoadRouteName - Route name of the load action providing data to the dataTable
 *
 * * datatableOptions ( parameters needed for the Datatable JS plugin configuration )
 * ** columnDefs - can stay empty but contains customize options for columns
 * ** order - Default order
 * ** columns - Column list to render the table
 *
 * Extra Events :
 *  You can add extra events that would be initialized on the init method, passing an array of objects mapping this model :
 *  {
 *      selector: ".className",
 *      event: "click",
 *      callback: a callback function
 *  }
 */
const DataList = DataList || {

    datatable: undefined,
    pageOptions: {
        exportDataLinkId: "#export_data",
        exportModalTitle: '',
        toggleElementRouteName: '',
        toggleElementClass: '.activation-toggle',
        asyncExport: false,
        dataSearchButtonId: "#data_search",
        dataResetButtonId: "#data_reset",
        datatableContainerClass: '.dataTable',
        searchFilterFormName: '',
        dataLoadRouteName: '',
        totalCountId: "#data_total",
    },
    datatableOptions: {},

    init: function(pageOptions, datatableOptions, extraEvents) {
        DataList.pageOptions = {...DataList.pageOptions, ...pageOptions};
        DataList.initDatatableOptions(datatableOptions)

        this.datatable = $(DataList.pageOptions.datatableContainerClass).DataTable(DataList.datatableOptions);

        $(document).on('click' ,'#line-select', function() {
            const checked = this.checked;
            $('.data-line').each(function() {
                $(this).prop('checked', checked);
            })
        });

        document.querySelectorAll(`form[name=${DataList.pageOptions.searchFilterFormName}] input`).forEach(function(element) {
            element.addEventListener('keyup', function(event) {
                if (event.keyCode === 13) {
                    DataList.datatable.draw();
                }
            });
        });

        $(document).on('click', DataList.pageOptions.dataResetButtonId, DataList.resetSearchForm);
        $(document).on(
            'click',
            DataList.pageOptions.exportDataLinkId,
            DataList.pageOptions.asyncExport ? DataList.asyncExportModal : DataList.exportData
        );
        $(document).on('click', DataList.pageOptions.dataSearchButtonId, DataList.redraw);
        $(document).on('click', DataList.pageOptions.toggleElementClass, DataList.toggleElementStatus)

        if (undefined !== extraEvents) {
            extraEvents.map((item) => {
                $(document).on(item.event, item.selector, item.callback);
            })
        }
    },

    redraw: () => {
        DataList.datatable.draw()
    },

    resetSearchForm: () => {
        let form = $(`form[name=${DataList.pageOptions.searchFilterFormName}]`);
        form.trigger('reset');
        form.find('.select2').val([]).change(); // resets for multiple select2
        DataList.redraw()
    },

    exportData: () => {
        let routeName = $(DataList.pageOptions.exportDataLinkId).data('route');

        if (typeof routeName != 'undefined') {
            $.ajax({
                type: "POST",
                url: Routing.generate(routeName),
                data: {"filters": $(`form[name=${DataList.pageOptions.searchFilterFormName}]`).serialize()},
                error: function (xhr) {
                    toastr.error(xhr.responseJSON.message);
                },
                success: function (response) {
                    let filename = 'export.csv';
                    let csvFile = new Blob([response], {type: "text/csv"});
                    let downloadLink = document.createElement("a");
                    downloadLink.download = filename;
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                }
            })
        }
    },

    asyncExportModal: () =>    {
        let modal = $("#modal");
        modal.find('.modal-title').text(DataList.pageOptions.exportModalTitle);
        modal.find('.modal-body').html(Translator.trans('referencial_products.export.modal.body'));
        modal.modal().show();

        $(document).on('click', '#modal-submit', DataList.asyncExportData);
    },

    asyncExportData: () => {
        let exportLink = $(DataList.pageOptions.exportDataLinkId);
        let routeName = exportLink.data('route');
        let modal = $('#modal');

        if (typeof routeName != 'undefined') {
            $.ajax({
                type: "POST",
                url: Routing.generate(routeName),
                data: {"filters": $(`form[name=${DataList.pageOptions.searchFilterFormName}]`).serialize()},
                error: function (xhr) {
                    toastr.error(xhr.responseJSON.message);
                },
                success: function (response) {
                    $(document).Toasts('create', {
                        class: 'bg-success',
                        title: DataList.pageOptions.exportModalTitle,
                        subtitle: '',
                        body: Translator.trans('modal.export.success'),
                    });
                    modal.find('.close').trigger('click');
                }
            })
        }
    },

    toggleElementStatus: (e) => {
        const icon = $(e.currentTarget);
        const id = icon.data('id');
        const url = Routing.generate(DataList.pageOptions.toggleElementRouteName, {id: id});
        $.ajax({
            type: 'PUT',
            url: url,
            success: function(response) {
                icon.replaceWith(response.content);
                toastr.success(response.message);
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON.message);
            }
        });
    },

    initDatatableOptions: function(extraOptions) {
        let datatableOptions= {
            columnDefs: [],
            order: [],
            columns: [],
            ajax: {
                type: "POST",
                url: Routing.generate(DataList.pageOptions.dataLoadRouteName),
                data: function (d) {
                    return $.extend({}, d, {
                        "filters": $(`form[name=${DataList.pageOptions.searchFilterFormName}]`).serialize(),
                    });
                },
                complete: function (xhr) {
                    $("#preloader").hide();
                    $(DataList.pageOptions.totalCountId).text(xhr.responseJSON.recordsFiltered);
                }
            },
            autoWidth: false,
            dom: "<'row col-md-12'<'col-md-4 inline'l><'col-sm-3'i><'col-sm-5'<'float-right'p>>>",
            fixedColumns: true,
            pageLength: 50,
            info: true,
            language: {
                url: '/build/json/datatable/fr-FR.json',
            },
            ordering: true,
            paging: true,
            responsive: true,
            searching: false,
            serverSide: true,
            createdRow: function (row, data, index) {
                let deleted = row.querySelector('.deleted-item');

                if (null !== deleted) {
                    $(row).removeAttr('class');
                    $(row).addClass('deleted');
                }
            },
            preDrawCallback: function () {
                $("#preloader").show();
            },
            drawCallback: function () {
                $("#preloader").hide();
            }
        }

        DataList.datatableOptions = {...datatableOptions, ...extraOptions}
    }
}

export default DataList