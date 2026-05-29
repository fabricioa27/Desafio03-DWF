package sv.edu.udb.Configuraciones;

import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Info;
import io.swagger.v3.oas.models.security.SecurityRequirement;
import io.swagger.v3.oas.models.security.SecurityScheme;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

//Clase para habilitar el uso de Autenticacion Bearer
@Configuration
public class SwaggerConfig {

    @Bean
    public OpenAPI customOpenAPI() {
        final String securitySchemeName = "bearerAuth"; //Este es el esquema que queremos habilitar
        return new OpenAPI()
                .info(new Info()
                        .title("API de Gestión de Eventos")
                        .version("1.0")
                        .description("API para la gestión de eventos, reservas y usuarios"))
                //Especificamos que requerimos un esquema de seguridad para Bearer JWT
                .addSecurityItem(new SecurityRequirement().addList(securitySchemeName))
                .components(new io.swagger.v3.oas.models.Components()
                        //Aqui configuramos el esquema de seguridad que acabamos de agregar a la lista
                        .addSecuritySchemes(securitySchemeName,
                                new SecurityScheme()
                                        .name(securitySchemeName)
                                        .type(SecurityScheme.Type.HTTP)
                                        .scheme("bearer") //tipo bearer
                                        .bearerFormat("JWT"))); //tipo JWT
        //Luego de configurar este bean, spring va a ver que el Objeto OpenAPI a sido modificado
        //y le da a swagger la configuracion para que pueda mostrar el esquema de seguridad
        // literalmente asi funcionan las configuraciones en Spring
    }
}
