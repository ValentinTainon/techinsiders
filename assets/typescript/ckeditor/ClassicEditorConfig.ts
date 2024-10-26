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
	WordCount
	// @ts-ignore
} from 'ckeditor5';
// @ts-ignore
import frTranslations from 'ckeditor5/translations/fr.js';
import CkWordCountUpdater from './CkWordCountUpdater.ts';

interface StarterConfig {
	codeBlock: { languages: { language: string; label: string }[] };
	language: { ui: string; content: string };
	link: { addTargetToExternalLinks: boolean; defaultProtocol: string };
	mention: { feeds: { marker: string; feed: string[] }[] };
	placeholder: string;
	plugins: any[];
	toolbar: { items: string[] };
	translations: any | null;
}

interface FeatureRichConfig {
	balloonToolbar: string[];
	codeBlock: { languages: { language: string; label: string }[] };
	fontFamily: { supportAllValues: boolean };
	fontSize: { options: (number | string)[]; supportAllValues: boolean };
	heading: { options: { model: string; view: string; title: string; class: string }[] };
	htmlSupport: { allow: { name: RegExp; styles: boolean; attributes: boolean; classes: boolean }[] };
	image: { toolbar: string[] };
	language: { ui: string; content: string; textPartLanguage: { title: string; languageCode: string }[] };
	link: { addTargetToExternalLinks: boolean; defaultProtocol: string; decorators: { toggleDownloadable: { mode: string; label: string; attributes: { download: string; }; }; }; };
	list: { properties: { styles: boolean; startIndex: boolean; reversed: boolean } };
	mention: { feeds: { marker: string; feed: string[] }[] };
	menuBar: { isVisible: boolean };
	placeholder: string;
	plugins: any[];
	simpleUpload: { uploadUrl: string; withCredentials: boolean; headers: { 'X-CSRF-TOKEN': string; Authorization: string; }; };
	style: { definitions: { name: string; element: string; classes: string[] }[] };
	toolbar: { items: string[]; shouldNotGroupWhenFull: boolean };
	table: { contentToolbar: string[] };
	translations: any | null;
	wordCount: { onUpdate: (stats: { characters: number; words: number }) => void; };
}

export default class ClassicEditorConfig {
	private editorType: string;
	private isDefaultLocale: boolean;

	constructor(editorType: string) {
		this.editorType = editorType;
		this.isDefaultLocale = document.documentElement.getAttribute('lang') === 'fr';
	}

	public config(): object {
		switch (this.editorType) {
			case 'starter':
				return this.starterConfig();
			case 'feature-rich':
				return this.featureRichConfig();
			default:
				throw new Error("Cannot load config due to invalid editor type.");
		}
	}

	private starterConfig(): StarterConfig {
		return {
			codeBlock: {
				languages: [
					{ language: 'bash', label: 'Bash' },
					{ language: 'c', label: 'C' },
					{ language: 'cpp', label: 'C++' },
					{ language: 'cs', label: 'C#' },
					{ language: 'css', label: 'CSS' },
					{ language: 'html', label: 'HTML' },
					{ language: 'java', label: 'Java' },
					{ language: 'javascript', label: 'JavaScript' },
					{ language: 'php', label: 'PHP' },
					{ language: 'plaintext', label: 'Plain text' },
					{ language: 'python', label: 'Python' },
					{ language: 'typescript', label: 'TypeScript' }
				]
			},
			language: {
				ui: this.isDefaultLocale ? 'fr' : 'en',
				content: this.isDefaultLocale ? 'fr' : 'en'
			},
			link: {
				addTargetToExternalLinks: true,
				defaultProtocol: 'https://'
			},
			mention: {
				feeds: [
					{
						marker: '@',
						feed: [
							/* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
						]
					}
				]
			},
			placeholder: this.isDefaultLocale ? 'Tapez votre contenu ici !' : 'Type your content here!',
			plugins: [
				Bold,
				Code,
				CodeBlock,
				Essentials,
				Italic,
				Link,
				Mention,
				Paragraph
			],
			toolbar: {
				items: [
					'undo',
					'redo',
					'|',
					'bold',
					'italic',
					'link',
					'|',
					'code',
					'codeBlock'
				]
			},
			translations: this.isDefaultLocale ? frTranslations : null,
		}
	}

	private featureRichConfig(): FeatureRichConfig {
		return {
			balloonToolbar: ['bold', 'italic', '|', 'link', 'insertImage', '|', 'bulletedList', 'numberedList'],
			codeBlock: {
				languages: [
					{ language: 'bash', label: 'Bash' },
					{ language: 'c', label: 'C' },
					{ language: 'cpp', label: 'C++' },
					{ language: 'cs', label: 'C#' },
					{ language: 'css', label: 'CSS' },
					{ language: 'html', label: 'HTML' },
					{ language: 'java', label: 'Java' },
					{ language: 'javascript', label: 'JavaScript' },
					{ language: 'php', label: 'PHP' },
					{ language: 'plaintext', label: 'Plain text' },
					{ language: 'python', label: 'Python' },
					{ language: 'typescript', label: 'TypeScript' }
				]
			},
			fontFamily: {
				supportAllValues: true
			},
			fontSize: {
				options: [10, 12, 14, 'default', 18, 20, 22],
				supportAllValues: true
			},
			heading: {
				options: [
					{
						model: 'paragraph',
						view: 'p',
						title: 'Paragraph',
						class: 'ck-heading_paragraph'
					},
					{
						model: 'heading1',
						view: 'h1',
						title: 'Heading 1',
						class: 'ck-heading_heading1'
					},
					{
						model: 'heading2',
						view: 'h2',
						title: 'Heading 2',
						class: 'ck-heading_heading2'
					},
					{
						model: 'heading3',
						view: 'h3',
						title: 'Heading 3',
						class: 'ck-heading_heading3'
					},
					{
						model: 'heading4',
						view: 'h4',
						title: 'Heading 4',
						class: 'ck-heading_heading4'
					},
					{
						model: 'heading5',
						view: 'h5',
						title: 'Heading 5',
						class: 'ck-heading_heading5'
					},
					{
						model: 'heading6',
						view: 'h6',
						title: 'Heading 6',
						class: 'ck-heading_heading6'
					}
				]
			},
			htmlSupport: {
				allow: [
					{
						name: /^.*$/,
						styles: true,
						attributes: true,
						classes: true
					}
				]
			},
			image: {
				toolbar: [
					'toggleImageCaption',
					'imageTextAlternative',
					'|',
					'imageStyle:wrapText',
					'imageStyle:breakText',
					'|',
					'linkImage',
					'|',
					'resizeImage'
				]
			},
			language: {
				ui: this.isDefaultLocale ? 'fr' : 'en',
				content: this.isDefaultLocale ? 'fr' : 'en',
				textPartLanguage: [
					{ title: this.isDefaultLocale ? 'Français' : 'French', languageCode: 'fr' },
					{ title: this.isDefaultLocale ? 'Anglais' : 'English', languageCode: 'en' }
				]
			},
			link: {
				addTargetToExternalLinks: true,
				defaultProtocol: 'https://',
				decorators: {
					toggleDownloadable: {
						mode: 'manual',
						label: 'Downloadable',
						attributes: {
							download: 'file'
						}
					}
				}
			},
			list: {
				properties: {
					styles: true,
					startIndex: true,
					reversed: true
				}
			},
			mention: {
				feeds: [
					{
						marker: '@',
						feed: [
							/* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
						]
					}
				]
			},
			menuBar: {
				isVisible: true
			},
			placeholder: this.isDefaultLocale ? 'Tapez votre contenu ici !' : 'Type your content here!',
			plugins: [
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
				WordCount
			],
			simpleUpload: {
				uploadUrl: '/upload',
				withCredentials: true,
				headers: {
					'X-CSRF-TOKEN': 'CSRF-Token',
					Authorization: 'Bearer <JSON Web Token>'
				}
			},
			style: {
				definitions: [
					{
						name: 'Article category',
						element: 'h3',
						classes: ['category']
					},
					{
						name: 'Title',
						element: 'h2',
						classes: ['document-title']
					},
					{
						name: 'Subtitle',
						element: 'h3',
						classes: ['document-subtitle']
					},
					{
						name: 'Info box',
						element: 'p',
						classes: ['info-box']
					},
					{
						name: 'Side quote',
						element: 'blockquote',
						classes: ['side-quote']
					},
					{
						name: 'Marker',
						element: 'span',
						classes: ['marker']
					},
					{
						name: 'Spoiler',
						element: 'span',
						classes: ['spoiler']
					},
					{
						name: 'Code (dark)',
						element: 'pre',
						classes: ['fancy-code', 'fancy-code-dark']
					},
					{
						name: 'Code (bright)',
						element: 'pre',
						classes: ['fancy-code', 'fancy-code-bright']
					}
				]
			},
			table: {
				contentToolbar: [
					'tableColumn',
					'tableRow',
					'mergeTableCells',
					'tableCellProperties',
					'tableProperties'
				]
			},
			toolbar: {
				items: [
					'undo',
					'redo',
					'|',
					'findAndReplace',
					'selectAll',
					'|',
					'alignment',
					'indent',
					'outdent',
					'|',
					'horizontalLine',
					'|',
					'fontSize',
					'fontFamily',
					'fontColor',
					'fontBackgroundColor',
					'highlight',
					'|',
					'bold',
					'italic',
					'underline',
					'strikethrough',
					'removeFormat',
					'|',
					'specialCharacters',
					'|',
					'bulletedList',
					'numberedList',
					'todoList',
					'|',
					'code',
					'codeBlock',
					'blockQuote',
					'insertTable',
					'|',
					'link',
					'insertImage',
					'mediaEmbed',
					'htmlEmbed',
					'|',
					'heading',
					'|',
					'style',
					'|',
					'textPartLanguage',
					'|',
					'sourceEditing',
					'showBlocks'
				],
				shouldNotGroupWhenFull: true
			},
			translations: this.isDefaultLocale ? frTranslations : null,
			wordCount: {
				onUpdate: (stats: { characters: number; words: number }) => {
					CkWordCountUpdater.updateStats(stats);
				}
			}
		};
	}
}