// @ts-ignore
import { Controller } from "@hotwired/stimulus";
import { FeatureRichClassicEditor } from "./../typescript/ckeditor5/editor-types/FeatureRichClassicEditor.ts";
import { EditorWordCounter } from "../typescript/ckeditor5/utils/EditorWordCounter.ts";
import { EditorMediaCleaner } from "../typescript/ckeditor5/utils/EditorMediaCleaner.ts";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  declare element: HTMLTextAreaElement;
  declare editor: FeatureRichClassicEditor;

  async connect(): Promise<void> {
    try {
      const isFrLocale: boolean = document.documentElement.lang === "fr";
      const editorWordCounter = new EditorWordCounter(
        Number(this.element.dataset.minPostLengthLimit)
      );

      this.editor = await FeatureRichClassicEditor.create(
        this.element,
        FeatureRichClassicEditor.getDefaultConfig(
          isFrLocale,
          this.element.dataset.postUuid,
          editorWordCounter
        )
      );

      editorWordCounter.handlePostLengthValidation();
      new EditorMediaCleaner(this.element.dataset).cleanUnusedImages();
    } catch (error) {
      this.disconnect();
      alert(error);
      window.location.replace(
        window.location.href.split("/").slice(0, 6).join("/")
      );
    }
  }

  disconnect(): void {
    try {
      this.editor.destroy();
    } catch (error) {
      console.error(error);
    }
  }
}
