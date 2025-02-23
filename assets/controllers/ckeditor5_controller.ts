// @ts-ignore
import { Controller } from "@hotwired/stimulus";
// @ts-ignore
import { ClassicEditor } from "ckeditor5";
import { EditorFactory } from "../typescript/ckeditor5/editor-types/EditorFactory.ts";

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLTextAreaElement> {
  declare element: HTMLTextAreaElement;
  declare editor: ClassicEditor;

  connect(): void {
    const editorType: string = String(this.element.dataset.editorType);

    EditorFactory.createEditor(this.element, editorType)
      .then((editor) => {
        this.editor = editor;
      })
      .catch((error: Error) => {
        this.disconnect();
        console.error(`Editor ${editorType} error: `, error);
        alert(error);
      });
  }

  disconnect(): void {
    if (!this.editor) return;

    // @ts-ignore
    this.editor.destroy().catch((error: Error) => {
      console.error(error);
    });
  }
}
