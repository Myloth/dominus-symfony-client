import Routing from 'fos-router'
import $ from "jquery";
import 'datatables.net-dt'
import type { Config, Api } from 'datatables.net-dt'
import { ListPageOptions } from '../types/ListPageOptions.ts';

export class ListPage {
    private datatable: Api<any>;
    private pageOptions: ListPageOptions;
    private datatableOptions: Config;

    constructor(datatableOptions: Config, customPageOptions: object | null = null) {
        this.pageOptions = this.initDefaultPageOptions(customPageOptions);
        this.datatableOptions = this.initDefaultDatatableConfig(datatableOptions);
        this.datatable = $((this.pageOptions.datatableContainerClass as string)).DataTable(this.datatableOptions);

        document.addEventListener('click', (event) => {
            const target = event.target as HTMLElement;
            if (target) {
                if (target.closest(this.pageOptions.dataSearchButtonId as string)) {
                    this.redraw();
                } else if (target.closest(this.pageOptions.dataResetButtonId as string)) {
                    this.resetSearchForm();
                }
            }
        });
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
                data: function (d: any) {
                    const form = document.querySelector(`form[name=${pageOptions.searchFilterFormName}]`) as HTMLFormElement;
                    const serialized = form ? new URLSearchParams(new FormData(form) as any).toString() : '';
                    return Object.assign({}, d, {
                        "filters": serialized,
                    });
                },
                complete: function (xhr) {
                    const preloader = document.getElementById("preloader");
                    if (preloader) {
                        preloader.style.display = "none";
                    }
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
                    row.removeAttribute('class');
                    row.classList.add('deleted');
                }
            },
            preDrawCallback: function () {
                const preloader = document.getElementById("preloader");
                if (preloader) {
                    preloader.style.display = "block";
                }
            },
            drawCallback: function () {
                const preloader = document.getElementById("preloader");
                if (preloader) {
                    preloader.style.display = "none";
                }
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
        const form = document.querySelector(`form[name=${this.pageOptions.searchFilterFormName}]`) as HTMLFormElement;
        if (form) {
            form.reset();
            form.querySelectorAll<any>('.select2').forEach((select) => {
                if (select.tomselect) {
                    select.tomselect.clear();
                }
            });
        }
        this.redraw();
    }
}