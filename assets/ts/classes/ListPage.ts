import Routing from 'fos-router'
import $ from "jquery";
import 'datatables.net-dt'
import type { Config, Api } from 'datatables.net-dt'
import { ListPageOptions } from '../types/ListPageOptions';

export class ListPage {
    private datatable: Api<any>;
    private pageOptions: ListPageOptions;
    private datatableOptions: Config;

    constructor(datatableOptions: Config, customPageOptions: object | null = null) {
        this.pageOptions = this.initDefaultPageOptions(customPageOptions);
        this.datatableOptions = this.initDefaultDatatableConfig(datatableOptions);
        this.datatable = $((this.pageOptions.datatableContainerClass as string)).DataTable(this.datatableOptions);

        $(document).on('click', this.pageOptions.dataSearchButtonId as string, () => this.redraw());
        $(document).on('click', this.pageOptions.dataResetButtonId as string, () => this.resetSearchForm());
    }


    initDefaultDatatableConfig(customOptions: Config): Config
    {
        const pageOptions: ListPageOptions = this.pageOptions
        const defaultOptions: Config = {
            columnDefs: [],
            order: [],
            columns: [],
            ajax: {
                type: "POST",
                url: Routing.generate(pageOptions.dataLoadRouteName),
                data: function (d) {
                    return $.extend({}, d, {
                        "filters": $(`form[name=${pageOptions.searchFilterFormName}]`).serialize(),
                    });
                },
                complete: function (xhr) {
                    $("#preloader").hide();
                }
            },
            autoWidth: false,
//            dom: "<'row col-md-12'<'col-md-4 inline'l><'col-sm-3'i><'col-sm-5'<'float-right'p>>>",
            pageLength: 50,
            info: true,
            language: {
                url: '/build/json/datatable/fr-FR.json',
            },
            ordering: true,
            paging: true,
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

        return {...defaultOptions, ...customOptions}
    }

    initDefaultPageOptions(customPageOptions: object | null): ListPageOptions
    {
        const defaultPageOptions = {
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
        };

        if (!customPageOptions) {
            return defaultPageOptions
        }

        return {...defaultPageOptions, ...customPageOptions}
    }

    redraw(): void
    {
        this.datatable.draw();
    }

    resetSearchForm(): void
    {
        let form = $(`form[name=${this.pageOptions.searchFilterFormName}]`);
        form.trigger('reset');
        form.find('.select2').val([]).trigger('change'); // resets for multiple select2
        this.redraw()
    }
}