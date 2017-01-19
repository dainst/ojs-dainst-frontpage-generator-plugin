# ojs-dainst-frontpage-generator-plugin
a plugin for OJS, wich can create frontpages for our PDFs and write metadata into them


@submodules:
tcpdf

@OJS
Version: 2.4.8

@Need the following programms:
* TeX / LuaLaTeX
* pdftk
* exiftools


## LuaLaTeX-Commands
### Passing an option to the pdf do this in the terminal

`lualatex "\def\CMD{CONTENT}...\input{dai-fm-a4.tex}"`

`CMD` stands for the command/option; `CONTENT` for the string

### Following options are available
Defined by `\def\CMD{CONTENT}`

- `\def\arttitle{ARTICLE TITLE}`
-  `\def\artauthor{ARTICLE AUTHOR}`
-  `\def\journal{JOURNAL NAME}`
-  `\def\journalsubtitle{JOURNAL SUBTITLE}`
-  `\def\urn{URN}`
-  `\def\purl{LINK}`
-  `\def\pages{PAGES}`
-  `\def\yearpub{YEAR}`
-  `\def\issn{PRINT ISSN}`
-  `\def\issnonline{ONLINE ISSN}`
-  `\def\journalurl{LINK TO JOURNAL}`
-  `\def\issue{ISSUE}`
-  `\def\publisher{PUBLISHER}`
-  `\def\inputpdf{PATH TO PDF WHICH SHOULD BE INCLUDED}`
