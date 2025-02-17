import {
  AccessibilityHelp,
  Alignment,
  AutoImage,
  AutoLink,
  BalloonToolbar,
  BlockQuote,
  Bold,
  ClassicEditor,
  Code,
  CodeBlock,
  Essentials,
  FindAndReplace,
  FontBackgroundColor,
  FontColor,
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

// Custom Plugins
import { SimpleUploadCleaner } from "../editor-config/custom-plugins/SimpleUploadCleaner.ts";
import { EditorWordCounter } from "../utils/EditorWordCounter.ts";

// Styles
import "ckeditor5/dist/ckeditor5.css";
import "../../../styles/admin/ckeditor5/custom.css";

// Properties
import { BalloonToolbarProperty } from "../editor-config/properties/balloon-toolbar/BalloonToolbarProperty.ts";
import { CodeBlockProperty } from "../editor-config/properties/code-block/CodeBlockProperty.ts";
import { FontSizeProperty } from "../editor-config/properties/font-size/FontSizeProperty.ts";
import { HeadingProperty } from "../editor-config/properties/heading/HeadingProperty.ts";
import { HtmlSupportProperty } from "../editor-config/properties/html-support/HtmlSupportProperty.ts";
import { ImageProperty } from "../editor-config/properties/image/ImageProperty.ts";
import { LanguageProperty } from "../editor-config/properties/language/LanguageProperty.ts";
import { LicenseKeyProperty } from "../editor-config/properties/license-key/LicenseKeyProperty.ts";
import { LinkProperty } from "../editor-config/properties/link/LinkProperty.ts";
import { ListProperty } from "../editor-config/properties/list/ListProperty.ts";
import { MentionProperty } from "../editor-config/properties/mention/MentionProperty.ts";
import { MenuBarProperty } from "../editor-config/properties/menu-bar/MenuBarProperty.ts";
import { PlaceholderProperty } from "../editor-config/properties/placeholder/PlaceholderProperty.ts";
import { RemovePluginsProperty } from "../editor-config/properties/remove-plugins/RemovePluginsProperty.ts";
import { SimpleUploadProperty } from "../editor-config/properties/simple-upload/SimpleUploadProperty.ts";
import { SimpleUploadCleanerProperty } from "./../editor-config/properties/simple-upload-cleaner/SimpleUploadCleanerProperty.ts";
import { StyleProperty } from "../editor-config/properties/style/StyleProperty.ts";
import { TableProperty } from "../editor-config/properties/table/TableProperty.ts";
import { ToolbarProperty } from "../editor-config/properties/toolbar/ToolbarProperty.ts";
import { TranslationsProperty } from "../editor-config/properties/translations/TranslationsProperty.ts";
import { WordCountProperty } from "../editor-config/properties/word-count/WordCountProperty.ts";

export class FeatureRichClassicEditor extends ClassicEditor {
  public static editorWordCounter: EditorWordCounter;

  public static builtinPlugins = [
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
    SimpleUploadCleaner,
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
  ];

  private static toolbarItems: Array<string> = [
    "undo",
    "redo",
    "|",
    "findAndReplace",
    "selectAll",
    "|",
    "alignment",
    "indent",
    "outdent",
    "|",
    "horizontalLine",
    "|",
    "fontSize",
    "fontColor",
    "fontBackgroundColor",
    "highlight",
    "|",
    "bold",
    "italic",
    "underline",
    "strikethrough",
    "removeFormat",
    "|",
    "specialCharacters",
    "|",
    "bulletedList",
    "numberedList",
    "todoList",
    "|",
    "blockQuote",
    "insertTable",
    "code",
    "codeBlock",
    "|",
    "link",
    "insertImage",
    "mediaEmbed",
    "htmlEmbed",
    "|",
    "heading",
    "|",
    "style",
    "|",
    "textPartLanguage",
    "|",
    "sourceEditing",
    "showBlocks",
    "|",
    "accessibilityHelp",
  ];

  public static getDefaultConfig(
    isFrLocale: boolean,
    uploadDir: string
  ): object {
    return {
      ...BalloonToolbarProperty.getConfig(),
      ...CodeBlockProperty.getConfig(),
      ...FontSizeProperty.getConfig(),
      ...HeadingProperty.getConfig(),
      ...HtmlSupportProperty.getConfig(),
      ...ImageProperty.getConfig(),
      ...LanguageProperty.getConfig(isFrLocale),
      ...LicenseKeyProperty.getConfig(),
      ...LinkProperty.getConfig(),
      ...ListProperty.getConfig(),
      ...MentionProperty.getConfig(),
      ...MenuBarProperty.getConfig(),
      ...PlaceholderProperty.getConfig(),
      ...RemovePluginsProperty.getConfig(),
      ...SimpleUploadProperty.getConfig(uploadDir),
      ...SimpleUploadCleanerProperty.getConfig(uploadDir),
      ...StyleProperty.getConfig(),
      ...TableProperty.getConfig(),
      ...ToolbarProperty.getConfig(FeatureRichClassicEditor.toolbarItems),
      ...TranslationsProperty.getConfig(isFrLocale),
      ...WordCountProperty.getConfig(
        FeatureRichClassicEditor.editorWordCounter
      ),
    };
  }
}
