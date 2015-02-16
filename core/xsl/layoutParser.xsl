<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >

<!--xsl:output method="html" version="4.0" encoding="UTF-8" indent="no" /-->

<xsl:import href="base.xsl"/>
<xsl:import href="header.xsl"/>

<xsl:import href="templateEngines/handlebars.xsl" use-when="$TEMPLATE_ENGINE='handlebars'"/>

<xsl:output
    method="html"
    doctype-public="XSLT-compat"
    omit-xml-declaration="yes"
    encoding="UTF-8"
    indent="yes" />
<xsl:template match="/">
<html>
<head>
    <xsl:call-template name="func_modulesHead">
    </xsl:call-template>

    <xsl:for-each select="page/head">
        <xsl:for-each select="css/item">
            <xsl:call-template name="func_renderCSS">
                <xsl:with-param name="href" select="."/>
            </xsl:call-template>
       </xsl:for-each>

       <xsl:for-each select="javascript/item">
            <xsl:call-template name="func_renderJS">
                <xsl:with-param name="src" select="."/>
            </xsl:call-template>
       </xsl:for-each>
 
    </xsl:for-each>
</head>

<body>

    <xsl:call-template name="func_modulesBodyStart">
    </xsl:call-template>

    <xsl:call-template name="func_modulesBodyEnd">
    </xsl:call-template>



</body>

</html>
</xsl:template>





</xsl:stylesheet>
