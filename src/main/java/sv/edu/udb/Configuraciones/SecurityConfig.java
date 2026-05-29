package sv.edu.udb.Configuraciones;

import lombok.AllArgsConstructor;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.AuthenticationProvider;
import org.springframework.security.authentication.dao.DaoAuthenticationProvider;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.method.configuration.EnableMethodSecurity;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import sv.edu.udb.Servicios.UserDetailsServiceImpl;

// Todos los beans creados aqui los ocupa Spring Security
// Sin estos beans, spring security no sabra que hacer
@AllArgsConstructor
@Configuration
@EnableWebSecurity //Me habilita configurar el filterChain para agregar mi JwtFilter
@EnableMethodSecurity //Habilito config de seguridad en todo mi proyecto (para ocupar preAuthorize, p.e)
public class SecurityConfig {

    private final UserDetailsService userDetailsService;
    private final JwtAuthenticationFilter jwtAuthenticationFilter;

    // Se crea un bean para passwordEncoder
    // Ocuparemos el BCryptPasswordEncoder porque es el mas utilizado y seguro
    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    // Bean que une passwordEncoder y UserDetailsService
    // Este bean es el que ocupa AuthenticationManager traer de la base de datos el usuario y comparar
    // la contraseña con la que viene del request
    @Bean
    public AuthenticationProvider authenticationProvider() {
        DaoAuthenticationProvider authProvider = new DaoAuthenticationProvider(); //DaoAuthenticationProvider es el proveedor estandar
        authProvider.setUserDetailsService(userDetailsService);
        authProvider.setPasswordEncoder(passwordEncoder());
        return authProvider;

    }

    // Bean muy importante que se encarga de autenticar el usuario
    // Este ocupa UserDetailsService y UserDetails. Por eso implementamos esas interfaces
    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration authenticationConfiguration) throws Exception {
        return authenticationConfiguration.getAuthenticationManager();
    }


    //Con el objeto HttpSecurity que me pasa spring, lo configuro
    // para especificar que endpoints los permito, y cuales no
    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        http
            .csrf(csrf -> csrf.disable())
            .authorizeHttpRequests(auth -> auth
                .requestMatchers("/api/auth/login", "/api/auth/register", "/api/auth/refresh-token").permitAll()
                .requestMatchers("/swagger-ui/**", "/swagger-ui.html", "/v3/api-docs/**", "/api-docs/**").permitAll()
                .anyRequest().authenticated()
            )
            .sessionManagement(session -> session
                .sessionCreationPolicy(SessionCreationPolicy.STATELESS) //No guarda estado de sesiones
            )
            .authenticationProvider(authenticationProvider()) //Le decimos cual es el proveedor de autenticacion
                //AQUI registramos nuestro nuevo filtro de JWT en el Filter Chain
            .addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

}