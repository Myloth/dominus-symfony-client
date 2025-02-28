import { CanEdit } from "../interfaces/CanEdit";
import Routing from "fos-router";
import {EditParams} from "../types/EditParams";

export class EditPage implements CanEdit {
    private form: HTMLFormElement;
    private saveButton: HTMLButtonElement;
    private saveRoute: string;
    private saveParams: object

    constructor(params: EditParams) {
        this.form = document.forms.namedItem(params.formName) as HTMLFormElement;
        this.saveButton = document.getElementById(params.saveButton) as HTMLButtonElement;
        this.saveRoute = params.saveRouteName;
        this.saveParams = {id: params.editId}

        this.saveButton.addEventListener('click', this.save.bind(this));
    }

    save(): void {
        let route: string = Routing.generate(this.saveRoute, this.saveParams)
        console.log(route);
    }
}