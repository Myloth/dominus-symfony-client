import { CanEdit } from "../interfaces/CanEdit.ts";
import Routing from "fos-router";
import {EditParams} from "../types/EditParams.ts";

export class EditPage implements CanEdit {
    private form: HTMLFormElement;
    private saveButton: HTMLButtonElement;
    private saveRoute: string;
    private saveParams: any;

    constructor(params: EditParams) {
        this.form = document.forms.namedItem(params.formName) as HTMLFormElement;
        this.saveButton = document.getElementById(params.saveButton) as HTMLButtonElement;
        this.saveRoute = params.saveRouteName;
        this.saveParams = {id: params.editId}

        this.saveButton.addEventListener('click', this.save.bind(this));
    }

    save(): void {
        let route: string = Routing.generate(this.saveRoute, this.saveParams)
        let formData = new FormData(this.form);

        fetch(route, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            let result: { id: number } = JSON.parse(data);
            let params = this.saveParams;
            params.id = result.id as number;
            window.location.href = Routing.generate(data, params);
        })
        .catch(error => {
            console.error(error);
        });
    }
}