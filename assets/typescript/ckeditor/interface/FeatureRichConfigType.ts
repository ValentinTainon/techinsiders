export default interface FeatureRichConfigType {
  balloonToolbar: string[];
  codeBlock: { languages: { language: string; label: string }[] };
  fontFamily: { supportAllValues: boolean };
  fontSize: { options: (number | string)[]; supportAllValues: boolean };
  heading: {
    options: { model: string; view: string; title: string; class: string }[];
  };
  htmlSupport: {
    allow: {
      name: RegExp;
      styles: boolean;
      attributes: boolean;
      classes: boolean;
    }[];
  };
  image: { toolbar: string[] };
  language: {
    ui: string;
    content: string;
    textPartLanguage: { title: string; languageCode: string }[];
  };
  licenseKey: string;
  link: {
    addTargetToExternalLinks: boolean;
    defaultProtocol: string;
    decorators: {
      toggleDownloadable: {
        mode: string;
        label: string;
        attributes: { download: string };
      };
    };
  };
  list: {
    properties: { styles: boolean; startIndex: boolean; reversed: boolean };
  };
  mention: { feeds: { marker: string; feed: string[] }[] };
  menuBar: { isVisible: boolean };
  placeholder: string;
  plugins: any[];
  removePlugins: string[];
  simpleUpload: {
    uploadUrl: string;
    withCredentials: boolean;
    headers: {
      "X-CSRF-TOKEN": string;
      Authorization: string;
      "Post-Uuid": string | undefined;
    };
  };
  style: {
    definitions: { name: string; element: string; classes: string[] }[];
  };
  toolbar: { items: string[]; shouldNotGroupWhenFull: boolean };
  table: { contentToolbar: string[] };
  translations: any | null;
  wordCount: {
    onUpdate: (stats: { characters: number; words: number }) => void;
  };
}
