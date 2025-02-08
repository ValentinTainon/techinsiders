// @ts-ignore
import { Controller } from "@hotwired/stimulus";
import { StarterClassicEditor } from "../typescript/ckeditor5/editor-types/StarterClassicEditor.ts";
import { FeatureRichClassicEditor } from "./../typescript/ckeditor5/editor-types/FeatureRichClassicEditor.ts";
import { EditorWordCounter } from "../typescript/ckeditor5/utils/EditorWordCounter.ts";
import { EditorMediaCleaner } from "../typescript/ckeditor5/utils/EditorMediaCleaner.ts";
import { EditorHelper } from "../typescript/ckeditor5/utils/EditorHelper.ts";
import { EditorType } from "../typescript/ckeditor5/editor-types/enum/EditorType.ts";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  declare element: HTMLTextAreaElement;
  declare editor: StarterClassicEditor | FeatureRichClassicEditor;

  async connect(): Promise<void> {
    const editorType = this.element.dataset.editorType;
    const isFrLocale: boolean = document.documentElement.lang === "fr";

    switch (editorType) {
      case EditorType.Starter:
        try {
          // @ts-ignore
          this.editor = await StarterClassicEditor.create(
            this.element,
            StarterClassicEditor.getDefaultConfig(isFrLocale)
          );
        } catch (error) {
          this.disconnect();
          alert(error);
          EditorHelper.redirectToPageIndex();
        }
        break;
      case EditorType.FeatureRich:
        try {
          const editorWordCounter = new EditorWordCounter(
            Number(this.element.dataset.minPostLengthLimit)
          );

          // @ts-ignore
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
          EditorHelper.redirectToPageIndex();
        }
        break;
      default:
        throw new Error("Invalid editor type");
    }
  }

  disconnect(): void {
    try {
      // @ts-ignore
      this.editor.destroy();
    } catch (error) {
      console.error(error);
    }
  }
}
