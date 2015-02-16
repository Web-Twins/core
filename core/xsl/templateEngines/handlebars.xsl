<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >

<xsl:template name="func_modulesHead">
    <script>
        <xsl:attribute name="src">
            <xsl:value-of select="$URL_HOME" /><xsl:text>core/static/javascript/jquery-1.11.2.min.js</xsl:text>
        </xsl:attribute>
    </script>
    <script>
        <xsl:attribute name="src">
            <xsl:value-of select="$URL_HOME" /><xsl:text>core/static/javascript/handlebars-v2.0.0.js</xsl:text>
        </xsl:attribute>
    </script>

    <script>
        function core_renderHandlerbars(id, templateUrl, modelUrl) {
            var source, template;
            var model;
            $.ajax({
                url: templateUrl,
                async: false,
                success: function (response) {
                    source = response;
                }
            });

            $.ajax({
                url: modelUrl,
                async: false,
                success: function (response) {
                    model = response;
                }
            });

            template = Handlebars.compile(source);
            //model = $.parseJSON(model);
            var html = template(model);
            $(document.body).append(html);
        }


    </script>


</xsl:template>

<xsl:template name="func_modulesBodyStart">
    <xsl:for-each select="//modules/module">
        <xsl:variable name="index" select="position()"/>
        <xsl:variable name="id">
            <xsl:value-of select="name" />
            <xsl:text>-</xsl:text>
            <xsl:value-of select="$index" />
        </xsl:variable>
        <script  type="text/x-handlebars-template">
            <xsl:attribute name="id">
                <xsl:value-of select="$id" />
            </xsl:attribute>
            <xsl:copy-of select="document(concat($PATH_TEMPLATE, path, 'views/', name, '.hb.html'))" disable-output-escaping="no"/>
        </script>

        <!--script  type="text/x-handlebars-model">
            <xsl:attribute name="id">
                <xsl:text>model-</xsl:text>
                <xsl:value-of select="$id" />
            </xsl:attribute>
            <xsl:copy-of select="document(concat($PATH_TEMPLATE, path, 'models/', model))" />
        </script-->




    </xsl:for-each>
</xsl:template>


<xsl:template name="func_modulesBodyEnd">
    <xsl:for-each select="//modules/module">
        <xsl:variable name="index" select="position()"/>
        <xsl:variable name="id">
            <xsl:value-of select="name" />
            <xsl:text>-</xsl:text>
            <xsl:value-of select="$index" />
        </xsl:variable>
        <script>
        core_renderHandlerbars('<xsl:value-of select="$id" />', '<xsl:value-of select="concat($URL_TEMPLATE, path, 'views/', name, '.hb.html')" />', '<xsl:value-of select="concat($URL_TEMPLATE, path, 'models/', model)" />');
        </script>
    </xsl:for-each>
</xsl:template>







</xsl:stylesheet>
