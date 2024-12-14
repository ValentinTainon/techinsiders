import {
  AccessibilityHelp,
  Alignment,
  AutoImage,
  AutoLink,
  BalloonToolbar,
  BlockQuote,
  Bold,
  Code,
  CodeBlock,
  Essentials,
  FindAndReplace,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  FullPage,
  GeneralHtmlSupport,
  Heading,
  Highlight,
  HorizontalLine,
  HtmlComment,
  HtmlEmbed,
  ImageBlock,
  ImageCaption,
  ImageInline,
  ImageInsert,
  ImageInsertViaUrl,
  ImageResize,
  ImageStyle,
  ImageTextAlternative,
  ImageToolbar,
  ImageUpload,
  Indent,
  IndentBlock,
  Italic,
  Link,
  LinkImage,
  List,
  ListProperties,
  MediaEmbed,
  MediaEmbedToolbar,
  Mention,
  Paragraph,
  PasteFromOffice,
  RemoveFormat,
  SelectAll,
  ShowBlocks,
  SimpleUploadAdapter,
  SourceEditing,
  SpecialCharacters,
  SpecialCharactersArrows,
  SpecialCharactersCurrency,
  SpecialCharactersEssentials,
  SpecialCharactersLatin,
  SpecialCharactersMathematical,
  SpecialCharactersText,
  Strikethrough,
  Style,
  Table,
  TableCaption,
  TableCellProperties,
  TableColumnResize,
  TableProperties,
  TableToolbar,
  TextPartLanguage,
  TextTransformation,
  TodoList,
  Underline,
  WordCount,
  // @ts-ignore
} from "ckeditor5";
// @ts-ignore
import frTranslations from "ckeditor5/translations/fr.js";
import StarterConfigType from "../interface/StarterConfigType.ts";

export default class EditorStarterConfig {
  private isDefaultLocale: boolean;

  constructor() {
    this.isDefaultLocale =
      document.documentElement.getAttribute("lang") === "fr";
  }

  public getConfig(): StarterConfigType {
    return {
      codeBlock: {
        languages: [
          { language: "bash", label: "Bash" },
          { language: "c", label: "C" },
          { language: "cpp", label: "C++" },
          { language: "cs", label: "C#" },
          { language: "css", label: "CSS" },
          { language: "html", label: "HTML" },
          { language: "java", label: "Java" },
          { language: "javascript", label: "JavaScript" },
          { language: "php", label: "PHP" },
          { language: "plaintext", label: "Plain text" },
          { language: "python", label: "Python" },
          { language: "typescript", label: "TypeScript" },
        ],
      },
      language: {
        ui: this.isDefaultLocale ? "fr" : "en",
        content: this.isDefaultLocale ? "fr" : "en",
      },
      link: {
        addTargetToExternalLinks: true,
        defaultProtocol: "https://",
      },
      mention: {
        feeds: [
          {
            marker: "@",
            feed: [
              /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
            ],
          },
        ],
      },
      placeholder: this.isDefaultLocale
        ? "Tapez votre contenu ici !"
        : "Type your content here!",
      plugins: [
        Bold,
        Code,
        CodeBlock,
        Essentials,
        Italic,
        Link,
        Mention,
        Paragraph,
      ],
      toolbar: {
        items: [
          "undo",
          "redo",
          "|",
          "bold",
          "italic",
          "link",
          "|",
          "code",
          "codeBlock",
        ],
      },
      translations: this.isDefaultLocale ? frTranslations : null,
    };
  }
}
