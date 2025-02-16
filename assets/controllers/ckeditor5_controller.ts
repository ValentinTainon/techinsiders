// @ts-ignore
import { Controller } from "@hotwired/stimulus";
import { EditorType } from "../typescript/ckeditor5/editor-types/enum/EditorType.ts";
import { EditorFactory } from "../typescript/ckeditor5/editor-types/EditorFactory.ts";
import { StarterClassicEditor } from "../typescript/ckeditor5/editor-types/StarterClassicEditor.ts";
import { FeatureRichClassicEditor } from "../typescript/ckeditor5/editor-types/FeatureRichClassicEditor.ts";
import { EditorWordCounter } from "../typescript/ckeditor5/utils/EditorWordCounter.ts";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  declare element: HTMLTextAreaElement;
  declare editor: StarterClassicEditor | FeatureRichClassicEditor;

  connect(): void {
    const editorDataset: DOMStringMap = this.element.dataset;
    const editorType: string = String(editorDataset.editorType);

    if (editorType === EditorType.FeatureRich) {
      FeatureRichClassicEditor.editorWordCounter = new EditorWordCounter(
        Number(editorDataset.minLengthLimit)
      );
    }

    EditorFactory.createEditor(this.element, editorType)
      .then((editor) => {
        this.editor = editor;
      })
      .catch((error: Error) => {
        this.disconnect();
        console.error(`Editor ${editorType}: `, error);
        alert(error);
      });
  }

  disconnect(): void {
    // @ts-ignore
    this.editor.destroy().catch((error: Error) => {
      console.error(error);
    });
  }
}
