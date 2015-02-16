<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >


<xsl:template name="func_renderCSS">
    <xsl:param name="href"/>
    <link>
       <xsl:attribute name="href">
            <xsl:value-of select="$URL_CSS" />
            <xsl:value-of select="$href" />
       </xsl:attribute>
       <xsl:attribute name="rel">
            <xsl:text>stylesheet</xsl:text>
       </xsl:attribute>
       <xsl:attribute name="type">
            <xsl:text>text/css</xsl:text>
       </xsl:attribute>
    </link>
</xsl:template>

<xsl:template name="func_renderJS">
    <xsl:param name="src"/>
    <script>
       <xsl:attribute name="src">
            <xsl:value-of select="$URL_JS" /> 
            <xsl:value-of select="$src" />
       </xsl:attribute>
       <xsl:attribute name="type">
            <xsl:text>text/javascript</xsl:text>
       </xsl:attribute>
    </script>
</xsl:template>

</xsl:stylesheet>



