// @ts-ignore
import { ClassicEditor } from "ckeditor5";
import { EditorType } from "./enum/EditorType.ts";
import { StarterClassicEditor } from "./StarterClassicEditor.ts";
import { FeatureRichClassicEditor } from "./FeatureRichClassicEditor.ts";

export class EditorFactory {
  static createEditor<T extends ClassicEditor>(
    sourceElement: HTMLTextAreaElement,
    editorType: string
  ): Promise<T> {
    const isFrLocale: boolean = document.documentElement.lang === "fr";
    const isReadOnly: boolean = Boolean(
      sourceElement.dataset.readOnly === "true"
    );

    switch (editorType) {
      case EditorType.Starter:
        // @ts-ignore
        return StarterClassicEditor.create(
          sourceElement,
          StarterClassicEditor.getDefaultConfig(isFrLocale, isReadOnly)
        );
      case EditorType.FeatureRich:
        const uploadDir = String(sourceElement.dataset.uploadDir);
        const minCharacters = Number(sourceElement.dataset.minCharacters);

        if (!uploadDir) {
          throw new Error("Missing media upload path parameter");
        }

        // @ts-ignore
        return FeatureRichClassicEditor.create(
          sourceElement,
          FeatureRichClassicEditor.getDefaultConfig(
            isFrLocale,
            isReadOnly,
            uploadDir,
            minCharacters
          )
        );
      default:
        throw new Error("Invalid editor type");
    }
  }
}
