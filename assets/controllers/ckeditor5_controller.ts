// @ts-ignore
import { Controller } from "@hotwired/stimulus";
import { EditorFactory } from "../typescript/ckeditor5/editor-types/EditorFactory.ts";
import { StarterClassicEditor } from "../typescript/ckeditor5/editor-types/StarterClassicEditor.ts";
import { FeatureRichClassicEditor } from "../typescript/ckeditor5/editor-types/FeatureRichClassicEditor.ts";
// @ts-ignore
import CKEditorInspector from "@ckeditor/ckeditor5-inspector";

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLTextAreaElement> {
  declare element: HTMLTextAreaElement;
  declare editor: StarterClassicEditor | FeatureRichClassicEditor;

  connect(): void {
    const editorType: string = String(this.element.dataset.editorType);

    EditorFactory.createEditor(this.element, editorType)
      .then((editor) => {
        this.editor = editor;
        CKEditorInspector.attach(editor);
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
