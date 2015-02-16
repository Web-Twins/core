<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >

<xsl:variable name="baseDoc" select="document('../../pageConfig/base/base.xml')"/>
<xsl:variable name="PATH_CSS" select="$baseDoc/base/paths/css" />
<xsl:variable name="PATH_JS" select="$baseDoc/base/paths/javascript" />
<xsl:variable name="PATH_TEMPLATE" select="$baseDoc/base/paths/template" />
<xsl:variable name="TEMPLATE_ENGINE" select="$baseDoc/base/templateEngine/name" />

<xsl:variable name="URL_HOME" select="$baseDoc/base/urlPaths/home" />
<xsl:variable name="URL_CSS" select="$baseDoc/base/urlPaths/css" />
<xsl:variable name="URL_JS" select="$baseDoc/base/urlPaths/jaavscript" />
<xsl:variable name="URL_TEMPLATE" select="$baseDoc/base/urlPaths/template" />



</xsl:stylesheet>
